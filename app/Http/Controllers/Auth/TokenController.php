<?php

namespace App\Http\Controllers\Auth;

use Auth, Validator;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;

class TokenController extends Controller
{
    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        try {
            
            $token = Auth::refresh();
            return response()->json([
                'message' => 'Your token has been successfully refreshed now!',
                'token' => $token,
                'expires_at' => getTokenExpiryTime(Auth::factory()->getTTL() * 60),
            ]);

        } catch (\Exception $e){
            return $this->errorUnauthorized($e->getMessage());
        }
    }

    public function userVerify(Request $request, $code)
    {
        $code = urldecode(trim($code));
        
        $user = User::whereConfirmationCode($code)->first();
        if (! $user) {
            $redirectUrl = env('FRONT_END_URL') . "/error";
            return redirect($redirectUrl);
        }

        $user->confirmation_code = NULL;
        $user->confirmed_at = date('Y-m-d H:i:s');
        $user->save();
        
        $redirectUrl = env('FRONT_END_URL') . "/login";
        return redirect($redirectUrl);
    }
}
