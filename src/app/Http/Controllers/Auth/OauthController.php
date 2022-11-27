<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Libraries\LaravelOauth\Contract as LaravelOauth;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OauthController extends Controller
{
    use AuthenticatesUsers;

    /** @var LaravelOauth $laravelOauth */
    private LaravelOauth $laravelOauth;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LaravelOauth $laravelOauth)
    {
        $this->laravelOauth = $laravelOauth;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect to login form oauth server.
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        return redirect($this->laravelOauth->getAuthorizationPageUri());
    }

    /**
     * Get token credential
     */
    public function callback(Request $request) {
        try {
            if (!$request->has('code')) {
                throw new UnauthorizedException("Authorization code is missing");
            }

            // Getting token from oauth server
            $this->laravelOauth->retrieveToken($request->query('code'));

            $me = $this->laravelOauth->getMe();
            $user = $this->upsertUser($me);

            if ($this->doLogin($user)) {
                return $this->sendLoginResponse($request);
            }
            return $this->sendFailedLoginResponse($request);
        } catch (\Exception $e) {
            return view('auth.error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function upsertUser(array $me): User 
    {
        return User::where([
            'email' => Arr::get($me, 'email', ''),
        ])->firstOr(function() use ($me) {
            return User::create([
                'name' => Arr::get($me, 'name', ''),
                'email' => Arr::get($me, 'email', ''),
                'email_verified_at' => now(),
                'password' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function doLogin(User $user): Bool {
        if (! $this->guard()->loginUsingId($user->id)) {
            return false;
        }

        return true;
    }
}