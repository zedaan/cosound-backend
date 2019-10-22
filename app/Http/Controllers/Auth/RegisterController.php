<?php

namespace App\Http\Controllers\Auth;

use Auth, DB, Exception, Storage, Validator;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Jobs\{DeleteMedia, ExtractThumbnail};
use App\Http\Controllers\Controller;
use App\Services\GetStreamService;
use App\Transformers\AuthTransformer;
use App\Contracts\AuthContract;

class RegisterController extends Controller
{
    protected $authContract, $getStreamService;

    public function __construct(AuthContract $authContract, GetStreamService $getStreamService)
    {
        $this->authContract = $authContract;
        $this->getStreamService = $getStreamService;
    }

    public function register(Request $request)
    {
        $getStreamService = $this->getStreamService;

    	$credentials = request([
    		'email',
    		'password',
    		'first_name',
            'last_name',
            'type',
            'artist_name',
            'dob',
            'address',
            'latitude',
            'longitude',
            'postal_code',
    		'social_links',
            'avatar',
            'genres'
    	]);

    	$rules = [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
    		'first_name' => 'required|max:255',
    		'type' => ['required', Rule::in(['musician', 'professional'])],
    		'artist_name' => 'required',
            'address' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'postal_code' => 'required|min:4',
            'avatar' => 'nullable|max:14648'
        ];
        
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $savedFiles = [];

        DB::beginTransaction();

        try {
            $avatar = NULL;
            if ($request->hasFile('avatar')) {
                $avatar = Storage::put('avatars', $request->file('avatar'), 'public');
                $savedFiles[] = $avatar;
            }

            $credentials['avatar'] = $avatar;

            $user = $this->authContract->create($credentials);
            $this->authContract->doAsyncVerification($user);

            if ($avatar)
                dispatch((new ExtractThumbnail($user->id))->onQueue('image'));

            $timelineToken = $getStreamService->getToken($user->id, 'timeline');
            $notificationToken = $getStreamService->getToken($user->id, 'notification');
            $messageToken = $getStreamService->getToken($user->id, 'message');

            $getStreamService->followUserFeed($user->id, $user->id);

            $credentials = request(['email', 'password']);

            $token = Auth::attempt($credentials);

            $authTransformer = new AuthTransformer;
            $user = $authTransformer->transform($user);
            
            DB::commit();
            return $this->response()->array([
                'message' => 'Thanks for signing up! Please check your e-mail to verify & complete your registration.',
                'token' => $token,
                'get_stream_token' => [
                    'timeline' => $timelineToken,
                    'notification' => $notificationToken,
                    'message' => $messageToken
                ],
                'expires_at' => getTokenExpiryTime(Auth::factory()->getTTL() * 60),
                'data' => $user
            ]);
        }
        catch (Exception $e) {
            \Log::info($e);
            DB::rollback();
            foreach ($savedFiles as $file)
                dispatch((new DeleteMedia($file))->onQueue('delete'));

            return $this->errorInternal("Couldn't register due to some internal error. Please try again!");
        }
    }
}
