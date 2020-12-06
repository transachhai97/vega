<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

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
    public function __construct()
    {
        $this->middleware("guest")->except("logout");
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->status) {
            toastr()->error(
                "Hey $user->name, Looks like your status is InActive!",
                "InActive"
            );

            Auth::logout();
            return redirect()->route("login");
        }

        Auth::logoutOtherDevices($request->password);

        $user->update([
            "last_login_at" => Carbon::now()->toDateTimeString(),
            "last_login_ip" => $request->getClientIp(),
        ]);

        toastr()->info("Hey $user->name, Welcome Back!", "Welcome");
    }

    protected function loggedOut(Request $request)
    {
        // Show success msg.
        toastr()->success("You have successfully logged out!", "Logout");
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $providerUser = Socialite::driver($provider)
            ->stateless()
            ->user();
        $user = User::whereEmail($providerUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                "email" => $providerUser->getEmail(),
                "name" => $providerUser->getName(),
                "password" => Hash::make(12345678),
            ]);
        }

        SocialAccount::updateOrCreate(
            [
                "social_id" => $providerUser->getId(),
                "social_provider" => "google",
            ],
            ["social_name" => $providerUser->getName(), "user_id" => $user->id]
        );

        if (!$user->status) {
            toastr()->error(
                "Hey $user->name, Looks like your status is InActive!",
                "InActive"
            );

            return redirect()->route("login");
        }

        Auth::login($user);

        toastr()->success(
            "You have successfully logged in with " . ucfirst($provider) . "!",
            "Login"
        );

        return redirect($this->redirectPath());
    }
}
