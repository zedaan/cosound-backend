<?php

namespace App\Http\Controllers\Auth;

use Auth, DB, Storage, Validator;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\{User, Country};
use App\Http\Controllers\BaseApiController;
use App\Jobs\{DeleteMedia, ExtractThumbnail};
use App\Contracts\AuthContract;
use App\Services\GetStreamService;
use App\Transformers\{SuggestionTransformer, UploadTransformer};

class UserController extends BaseApiController
{
    protected $user, $authContract, $getStreamService;

    protected $model = 'App\Models\User';
    protected $transformer = 'App\Transformers\UserTransformer';

    protected $allowedOrderFields = ['email', 'first_name', 'last_name', 'type', 'artist_name'];
    protected $searchFields = ['artist_name', 'email'];                 // first_name & last_name searched by full_name
    
    public function __construct(AuthContract $authContract, GetStreamService $getStreamService)
    {
        $this->user = Auth::user();

        $this->authContract = $authContract;
        $this->getStreamService = $getStreamService;
    }

    public function me(Request $request)
    {
        $user = $this->user;

        return $this->response->item($user, $this->transformer);
    }
    
    public function publicProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->response->errorNotFound("Specified User doesn't exist");
        }

        $fieldsToRemove = ['email', 'dob', 'country_id', 'postal_code', 'phone_numbers', 'social_links', 'confirmed_at', 'created_at'];

        foreach ($fieldsToRemove as $field) {
            unset($user->$field);
        }

        return $this->response()->array([
            'data' => $user
        ]);
    }

    public function suggestions(Request $request)
    {
        $per_page = 4;
        if ($request->input('perPage')) {
            $per_page = $request->input('perPage');
        }
        
        $user = $this->user;
        
        $lat = $user->latitude;
        $lon = $user->longitude;


        $suggestions = User::selectRaw("*,
                              ( 6371 * acos( cos( radians(?) ) *
                                cos( radians( latitude ) )
                                * cos( radians( longitude ) - radians(?)
                                ) + sin( radians(?) ) *
                                sin( radians( latitude ) ) )
                              ) AS distance")
                ->orderBy("distance")
                ->setBindings([$lat, $lon, $lat])
                ->where('id', "!=", $user->id)
                ->paginate($per_page);
   

        // $suggestions = User::where('country_id', $country_id)
        //                 ->where('id', "!=", $user->id)
        //                 ->select('id', 'first_name', 'last_name', 'artist_name', 'avatar')
        //                 ->paginate($per_page);

        return $this->response->paginator($suggestions, new SuggestionTransformer);
    }

    public function search(Request $request)
    {
        $perPage = $request->has('per_page') ? $request->input('per_page') : 5;
        $searchString = $request->has('query') ? '%' . $request->input('query') . '%' : "";
        
        $users = User::query();

        if ($searchString) {
            $users = filterResults($users, $searchString, $this->searchFields);
            $users = $users->orWhereRaw("CONCAT (first_name, ' ', last_name) like ?", [$searchString]);
        }

        if ($request->has('order'))
            $users = sortResults($users, $request->input('order'), $this->allowedOrderFields);
        else
            $users = $users->orderBy('created_at','desc');

        $users = $users->paginate($perPage);

        return $this->response->paginator($users, 'App\Transformers\Search\UserTransformer');
    }

    public function fetchUploads(Request $request, $type = null)
    {
        $user = $this->user;

        $perPage = 4;
        
        if ($type === "image") {
            $perPage = 6;
        }

        if ($request->input('perPage')) {
            $perPage = $request->input('perPage');
        }
        
        $uploads = $user->uploads()->orderBy('created_at', 'desc');

        if ($type) {
            $uploads = $uploads->where('file_type', $type);
        }

        $uploads = $uploads->paginate($perPage);

        return $this->response->paginator($uploads, new UploadTransformer);       
    }

    public function publicUploads(Request $request, $id, $type = null)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->response->errorNotFound("Specified User doesn't exist");
        }

        $perPage = 4;

        if ($type === "image") {
            $perPage = 6;
        }
        
        if ($request->input('perPage')) {
            $perPage = $request->input('perPage');
        }
        
        $uploads = $user->uploads()->orderBy('created_at', 'desc');
        
        if ($type) {
            $uploads = $uploads->where('file_type', $type);
        }

        $uploads = $uploads->paginate($perPage);

        return $this->response->paginator($uploads, new UploadTransformer);       
    }

    public function getFollowStatusByUser(Request $request, $id) {
        $user = $this->user;
        if ($user->isFollowing($id)) {
            return response()->json([
                'message' => 'followed'
            ]);
        }else {
            return response()->json([
                'message' => 'unfollowed'
            ]);
        }
    }

    public function follow(Request $request)
    {
        $user = $this->user;

        $id = request('id');

        if ($user->isFollowing($id)) {
            $user->followings()->detach($id);
            $this->getStreamService->unfollowUserFeed($id, $user->id);

            return response()->json([
                'message' => 'unfollowed'
            ]);
        }
        
        $user->followings()->attach($id);
        $this->getStreamService->followUserFeed($id, $user->id);
        
        return response()->json([
            'message' => 'followed'
        ]);   
    }

    public function updateAvatar(Request $request)
    {
        $user = $this->user;
        
        $credentials = request([
            'avatar'
        ]);

        $rules = [
            'avatar' => 'nullable|max:14648'
        ];

        $validator = Validator::make($credentials, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => "Validation error", 
                'error' => $validator->messages()
            ], 400);
        }

        $avatar = NULL;
        if ($request->hasFile('avatar')) {
            $avatar = Storage::put('avatars', $request->file('avatar'), 'public');
        }

        if (! is_null($user->avatar)) {
            dispatch((new DeleteMedia($user->getOriginal('avatar')))->onQueue('delete'));
        }

        $user->avatar = $avatar;

        // Delete previous thumbnail file
        if (! is_null($user->thumbnail)) {
            dispatch((new DeleteMedia($user->getOriginal('thumbnail')))->onQueue('delete'));
        }

        $user->thumbnail = NULL;
        $user->save();

        if ($request->hasFile('avatar')) {
            dispatch((new ExtractThumbnail($user->id))->onQueue('image'));
        }

        return $this->response->item($user, $this->transformer);
    }

    public function update(Request $request, $id=null)
    {
        $user = $this->user;

        $credentials = request([
    		'first_name',
            'last_name',
            'type',
            'artist_name',
            'bio',
            'dob',
            'address',
            'latitude',
            'longitude',
            'postal_code',
            'phone_numbers',
            'social_links',
            'genres'
    	]);

    	$rules = [
    		'first_name' => 'required|max:255',
    		'type' => ['required', Rule::in(['musician', 'professional'])],
    		'artist_name' => 'required',
            'address' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'postal_code' => 'required|min:4',
    	];
        
    	$validator = Validator::make($credentials, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $user = $this->authContract->update($user, $credentials);

        return $this->response->item($user, $this->transformer);
    }

    public function changePassword(Request $request, $id=null)
    {
        $user = $this->user;

        $data = request([
            'old_password',
            'new_password',
    	]);

    	$rules = [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
    	];

        $validator = Validator::make($data, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $response = $this->authContract->changePassword($user, $data);
        if (! $response['success']) {
            return response()->json([
                'message' => $response['message'],
            ], 400);
        }

        return response()->json([
            'message' => $response['message']
        ]);
    }
}
