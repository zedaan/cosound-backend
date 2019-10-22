<?php

namespace App\Http\Controllers\Auth;

use Auth, Exception, Socialite, Validator;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\GetStreamService;
use App\Transformers\AuthTransformer;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @Resource("")
 */
class LoginController extends Controller
{
    protected $getStreamService;

    public function __construct(GetStreamService $getStreamService)
    {
        $this->getStreamService = $getStreamService;
    }

    public function login()
    {
        $getStreamService = $this->getStreamService;

        $credentials = request(['email', 'password']);

        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        try {
            if (!$token = Auth::attempt($credentials)) {
                return $this->errorBadRequest('Invalid credentials');
            }

        } catch (JWTException $e) {
            return $this->errorInternal('Server error');
        }

        $user = Auth::user();

        $timelineToken = $getStreamService->getToken($user->id, 'timeline');
        $notificationToken = $getStreamService->getToken($user->id, 'notification');
        $messageToken = $getStreamService->getToken($user->id, 'message');

        $authTransformer = new AuthTransformer;
        $user = $authTransformer->transform($user);
        
        return $this->response()->array([
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

    public function loginViaToken(Request $request)
    {
        $user = Auth::user();
        $getStreamService = $this->getStreamService;

        $timelineToken = $getStreamService->getToken($user->id, 'timeline');
        $notificationToken = $getStreamService->getToken($user->id, 'notification');
        $messageToken = $getStreamService->getToken($user->id, 'message');

        $user = (new AuthTransformer)->transform($user);
        
        return $this->response()->array([
            'get_stream_token' => [
                'timeline' => $timelineToken,
                'notification' => $notificationToken,
                'message' => $messageToken
            ],
            'data' => $user
        ]);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback()
    {
        try {
            $socialUser = (array) Socialite::driver('google')->stateless()->user();

            $user = User::where('email',$socialUser['email'])->first();
            if ($user) {
                $token = str_rot13(Auth::login($user));
                $expiresAt = getTokenExpiryTime(Auth::factory()->getTTL() * 60);

                $redirectUrl = env('FRONT_END_URL') . "/social-callback/?token=$token&expires_at=$expiresAt";
                return redirect($redirectUrl);
            }

            $email = str_rot13($socialUser['email']);
            $first_name = str_rot13($socialUser['user']['name']['givenName']);
            $last_name = str_rot13($socialUser['user']['name']['familyName']);

            $redirectUrl = env('FRONT_END_URL') . "/social-callback/?email=$email&first_name=$first_name&last_name=$last_name";
            return redirect($redirectUrl);

        } catch (Exception $e) {

            $redirectUrl = env('FRONT_END_URL') . "/social-callback/?error=1&message=" . str_rot13("Some error occurred during login via Google.");
            return redirect($redirectUrl);
        }
    }

    public function facebookRedirect()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function facebookCallback()
    {
        try {
            $socialUser = (array) Socialite::driver('facebook')->stateless()->user();

            $user = User::where('email',$socialUser['email'])->first();
            if ($user) {

                $token = str_rot13(Auth::login($user));
                $expiresAt = getTokenExpiryTime(Auth::factory()->getTTL() * 60);

                $redirectUrl = env('FRONT_END_URL') . "/social-callback/?token=$token&expires_at=$expiresAt";
                return redirect($redirectUrl);
            }

            $email = str_rot13($socialUser['email']);
            $name = explode(" ",$socialUser['user']['name']);
            $first_name = str_rot13($name[0]);
            $last_name = "";

            for ($i=1; $i < sizeof($name); $i++) {
                $last_name = $last_name . " " . $name[$i];
            }

            $last_name = str_rot13(trim($last_name));

            $redirectUrl = env('FRONT_END_URL') . "/social-callback/?email=$email&first_name=$first_name&last_name=$last_name";
            return redirect($redirectUrl);

        } catch (Exception $e) {

            $redirectUrl = env('FRONT_END_URL') . "/social-callback/?error=1&message=" . str_rot13("Some error occurred during login via Facebook.");
            return redirect($redirectUrl);
        }
    }
}
