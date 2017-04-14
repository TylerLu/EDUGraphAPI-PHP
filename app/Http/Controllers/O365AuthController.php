<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;


use App\Config\SiteConstants;
use App\Services\AADGraphService;
use App\Services\CookieService;
use App\Services\OrganizationsService;
use App\Services\TokenCacheService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT\Token;
use Microsoft\Graph\Connect\Constants;
use Socialize;
use App\Services\UserService;


class O365AuthController extends Controller
{

    /**
     * This function is used for auth and redirect after O365 user login succeed.
     */
    public function oauth()
    {
        $user = Socialite::driver('O365')->user();
        $refreshToken = $user->refreshToken;
        $o365UserId = $user->id;
        $o365Email = $user->email;

        $tokensArray = (new TokenCacheService)->UpdateTokenWhenLogin($user,$refreshToken);
        $msGraphToken = \GuzzleHttp\json_decode($tokensArray,true)[Constants::RESOURCE_ID]['value'];
        $graph = new AADGraphService;
        $tenant = $graph->GetTenantByToken($msGraphToken);
        $tenantId = $graph->GetTenantId($tenant);
        $orgId = (new OrganizationsService)->CreateOrganization($tenant, $tenantId);
        $this->linkLocalUserToO365IfLogin($user, $o365Email, $o365UserId, $orgId);

        //If user exists on db, check if this user is linked. If linked, go to schools/index page, otherwise go to link page.
        //If user doesn't exists on db, add user information like o365 user id, first name, last name to session and then go to link page.
        $userInDB = User::where('o365UserId', $o365UserId)->first();
        if ($userInDB) {
            if(Auth::check() && $userInDB->email !=Auth::user()->email){
                return redirect('/link');
            }
            if (!$userInDB->isLinked() ) {
                return redirect('/link');
            }else {
                Auth::loginUsingId($userInDB->id);
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }
        } else {
            //Below sessions are used for link users and create new local accounts.
            $_SESSION[SiteConstants::Session_OrganizationId] = $orgId;
            $_SESSION[SiteConstants::Session_TenantId] = $tenantId;
            $_SESSION[SiteConstants::Session_Tokens_Array] = $tokensArray;
            $_SESSION[SiteConstants::Session_Refresh_Token] = $refreshToken;
            $_SESSION[SiteConstants::Session_O365_User_ID] = $o365UserId;
            $_SESSION[SiteConstants::Session_O365_User_Email] = $o365Email;
            $_SESSION[SiteConstants::Session_O365_User_First_name] = $user->user['givenName'];
            $_SESSION[SiteConstants::Session_O365_User_Last_name] = $user->user['surname'];
            return redirect('/link');
        }
    }



    /**
     * If an O365 user is linked and login to the site, after logout, go to this page directly for quick login.
     */
    public function o365LoginHint()
    {
        $cookieServices = new CookieService();
        $email = $cookieServices->GetCookiesOfEmail();
        $userName = $cookieServices->GetCookiesOfUsername();
        $data = ["email" => $email, "userName" => $userName];
        return view('auth.o365loginhint', $data);

    }

    public function o365Login()
    {
        return Socialize::with('O365')->redirect();
    }

    /**
     * This function is for O365 login hint page after a user clicks 'Login with a different account'. It will clean all cookies and then goes to login page.
     */
    public function differentAccountLogin()
    {
        $cookieServices = new CookieService();
        $cookieServices->ClearCookies();
        return redirect('/login');
    }

    /**
     * Return token array and will be insert into tokencache table.
     */
    private function getTokenArray($user, $msGraphTokenArray)
    {
        $ts = $user->accessTokenResponseBody['expires_on'];
        $date = new \DateTime("@$ts");
        $aadTokenExpires = $date->format('Y-m-d H:i:s');
        return (new TokenCacheService())->FormatToken($aadTokenExpires,$user->token,$msGraphTokenArray['expires'], $msGraphTokenArray['token']);
    }

    /**
     * If a local user is login, link O365 user with local user.
     * @param $user
     * @param $o365Email
     * @param $o365UserId
     * @param $orgId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function linkLocalUserToO365IfLogin($user, $o365Email, $o365UserId, $orgId)
    {
        if (Auth::check()) {

            //A local user must link to and o365 account that is not linked.
            if (User::where('o365Email', $o365Email)->first())
                return back()->with('msg', 'Failed to link accounts. The Office 365 account ' . $o365Email . ' is already linked to another local account.');

            UserService::SaveUserInfo($o365UserId,$o365Email,$user->user['givenName'],$user->user['surname'],$orgId);
            return redirect("/schools");
        }
    }
}
