<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;


use App\Http\Middleware\SocializeAuthMiddleware;
use App\Services\AADGraphService;
use App\Services\CookieService;
use App\Services\OrganizationsService;
use App\Services\TokenCacheService;
use App\Services\UserService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Microsoft\Graph\Connect\Constants;
use Socialize;


class O365AuthController extends Controller
{

    private $tokenCacheService;

    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheService();
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
        $refreshToken = $user->refreshToken;
        $addToken = $user->token;
        $o365UserId = $user->id;
        $o365Email = $user->email;

        $ts = $user->accessTokenResponseBody['expires_on'];
        $date = new \DateTime("@$ts");
        $aadTokenExpires = $date->format('Y-m-d H:i:s');
        $jsonArray = [
            Constants::AADGraph =>[
                "expiresOn" => $aadTokenExpires,
                "value"=>$addToken
            ]
        ];
        $this->tokenCacheService->cacheToken($o365UserId,$refreshToken,$jsonArray);

        $msGraphToken = $this->tokenCacheService->GetMSGraphToken($o365UserId);
        $graph = new AADGraphService;
        $tenant = $graph->GetTenantByToken($msGraphToken);
        $tenantId = $graph->GetTenantId($tenant);
        $orgId = (new OrganizationsService)->CreateOrganization($tenant, $tenantId);
        $this->linkLocalUserToO365IfLogin($user, $o365Email, $o365UserId, $orgId);

        //If user exists on db, check if this user is linked. If linked, go to schools/index page, otherwise go to link page.
        //If user doesn't exists on db, add user information like o365 user id, first name, last name to session and then go to link page.
        $userInDB = User::where('o365UserId', $o365UserId)->first();
        if ($userInDB) {
            SocializeAuthMiddleware::removeSocializeSessions();
            if (Auth::check() && $userInDB->email != Auth::user()->email) {
                return redirect('/link');
            }
            if (!$userInDB->isLinked()) {
                return redirect('/link');
            } else {
                Auth::loginUsingId($userInDB->id);
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }
        } else {
            SocializeAuthMiddleware::setSocializeSessions($user, $orgId, $tenantId);
            return redirect('/link');
        }
    }

    /**
     * Show the special login page for linked O365 user.
     */
    public function o365LoginHint()
    {
        $cookieServices = new CookieService();
        $email = $cookieServices->GetCookiesOfEmail();
        $userName = $cookieServices->GetCookiesOfUsername();
        $data = ["email" => $email, "userName" => $userName];
        return view('auth.o365loginhint', $data);
    }

    /**
     * Redirect current user to normal login page.
     */
    public function differentAccountLogin()
    {
        $cookieServices = new CookieService();
        $cookieServices->ClearCookies();
        return redirect('/login');
    }

    /**
     * If a local user is login, link his/her account to an O365 account.
     */
    private function linkLocalUserToO365IfLogin($user, $o365Email, $o365UserId, $orgId)
    {
        if (Auth::check()) {

            //A local user must link to and o365 account that is not linked.
            if (User::where('o365Email', $o365Email)->first())
                return back()->with('msg', 'Failed to link accounts. The Office 365 account ' . $o365Email . ' is already linked to another local account.');

            (new UserService)->saveCurrentLoginUserInfo($o365UserId, $o365Email, $user->user['givenName'], $user->user['surname'], $orgId);
            SocializeAuthMiddleware::removeSocializeSessions();
            return redirect("/schools");
        }
    }


}
