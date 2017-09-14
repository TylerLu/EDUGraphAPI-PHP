<?php

namespace App\Http\Controllers;
use App\Http\Middleware\SocializeAuthMiddleware;
use Laravel\Socialite\Facades\Socialite;
use Socialize;

class O365AuthController extends Controller
{

    public function __construct()
    {

    }
    /**
     * Redirect the user to the OAuth Provider.
     */
    public function o365Login()
    {
        return Socialize::with('O365')->redirect();
    }

    /**
     * Handle provider callback.
     */
    public function oauth()
    {
        $user = Socialite::driver('O365')->user();
        SocializeAuthMiddleware::setSocializeSessions($user, '', '');
        return redirect("/schools");
    }

}