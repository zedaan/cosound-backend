<?php

namespace App\Http\Controllers\Admin\User;

use Auth, Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Mails\AdminStatusChangeEMail;
use App\Models\User;

class UserController extends Controller
{
    protected $transformer = 'App\Transformers\Admin\UserTransformer';

    protected $allowedOrderFields = ['email', 'first_name', 'last_name', 'type', 'artist_name', 'admin'];
    protected $searchFields = ['artist_name', 'email'];                 // first_name & last_name searched by full_name

    public function fetchUsers(Request $request)
    {
        $perPage = $request->has('per_page') ? $request->input('per_page') : 15;
        $searchString = $request->has('query') ? '%' . $request->input('query') . '%' : "";

        $users = User::query();

        if ($searchString) {
            $users = filterResults($users, $searchString, $this->searchFields);
            $users = $users->orWhereRaw("CONCAT (first_name, ' ', last_name) like ?", [$searchString]);
        }

        if (Input::has('order'))
            $users = sortResults($users, Input::get('order'), $this->allowedOrderFields);
        else
            $users = $users->orderBy('created_at','desc');
            
        $users = $users->paginate($perPage);

        return $this->response->paginator($users, $this->transformer);
    }

    public function fetchUserById(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user)
            return $this->errorNotFound('User doesn\'t exist!');

        return $this->response->item($user, $this->transformer);
    }

    public function toggleAdminStatus(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user)
            return $this->errorNotFound('User doesn\'t exist!');

        $user->admin = !$user->admin;
        $user->save();  
        \Log::info($user->email);
        \Log::info($user->first_name);

        Mail::to(
			$user->email,
			$user->first_name
		)->queue((new AdminStatusChangeEMail(Auth::user()->id, $user->id, $user->admin))->onQueue('mail'));

        return $this->response->item($user, $this->transformer);
    }
}
