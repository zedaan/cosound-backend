<?php

namespace App\Http\Controllers\Auth;

use Validator;

use Illuminate\Http\Request;
use App\Contracts\AuthContract;
use App\Http\Controllers\Controller;

class PasswordController extends Controller
{
	public $contract;

	public function __construct(AuthContract $contract)
	{
		$this->contract = $contract;
	}

	public function forgot(Request $request)
    {
    	$credentials = $request->only(['email']);

    	$rules = [
    		'email' => 'required|email'
    	];

    	$validator = Validator::make($credentials, $rules);
    	if ($validator->fails()) {
    		return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->errors()
            ], 400);
    	}

		$result = $this->contract->doAsyncForgotPassword($credentials);
		
		if (! $result) {
			return $this->errorBadRequest('E-mail not registered!');
		}

        return $this->response()->array([
			'message' => 'We\'ve sent you a mail with the reset password link.'
        ]);
    }

    public function reset($code)
    {
		$code = urldecode(trim($code));
		
    	$response = $this->contract->getResetPassword($code);
    	if (! $response['success']) {

			$redirectUrl = env('FRONT_END_URL') . "/error";
			return redirect($redirectUrl);
    	}
		
		$redirectUrl = env('FRONT_END_URL') . "/reset-password?token=$code";
		return redirect($redirectUrl);
    }

    public function postReset(Request $request)
    {
    	$credentials = $request->only(['email', 'password', 'password_confirmation', 'token']);
    	
    	$rules = [
    		'email' => 'required|email',
    		'password' => 'required|min:6|max:255|confirmed',
    		'token' => 'required'
    	];

    	$validator = Validator::make($credentials, $rules);
    	if ($validator->fails()) {
    		return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->errors()
            ], 400);
    	}

		$response = $this->contract->postResetPassword($credentials);
		if (! $response['success']) {
			return $this->errorBadRequest($response['message']);
		}
			
		return $this->response()->array([
			'message' => $response['message']
		]);
    }
}
