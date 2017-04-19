<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;

use App\Config\SiteConstants;
use App\Config\UserType;
use App\Http\Middleware\SocializeAuthMiddleware;
use App\Services\UserRolesService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Model\TokenCache;
use App\Services\TokenCacheService;
use App\Services\AADGraphService;


class LinkController extends Controller
{
    private $userServices;

    public function __construct()
    {
        $this->userServices = new UserService();
    }

    public function index()
    {

        $isLocalUserExists = false;
        $areAccountsLinked = false;
        $showLinkToExistingO365Account = false;
        $localUserEmail = '';
        $o365UserEmailInDB = '';
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        if ($user->userType === UserType::O365) {
            $o365userId = $user->o365UserId;
            if ($o365userId) {
                $user = $this->userServices->getUserByEmail($user->o365Email);
                if ($user) {
                    $isLocalUserExists = true;
                    $localUserEmail = $user->email;
                }

                $roles = (new AADGraphService)->GetCurrentUserRoles($o365userId, (new TokenCacheService)->GetMSGraphToken($o365userId));
                (new UserRolesService)->CreateOrUpdateUserRoles($roles, $o365userId);
            }
        } else {
            $user = Auth::user();
            $o365UserIdInDB = $user->o365UserId;
            $o365UserEmailInDB = $user->o365Email;
            $localUserEmail = $user->email;
            if (!$o365UserEmailInDB || !$o365UserIdInDB || $o365UserEmailInDB === '' || $o365UserIdInDB === '') {
                //Local user login but not linked. Should show link to existing o365 account link and then login to o365.
                $showLinkToExistingO365Account = true;
            } else {
                $areAccountsLinked = true;
            }
        }

        $arrData = array(
            'isLocalUserExists' => $isLocalUserExists,
            'areAccountsLinked' => $areAccountsLinked,
            'localUserEmail' => $localUserEmail,
            'o365UserEmail' => $o365UserEmailInDB,
            'showLinkToExistingO365Account' => $showLinkToExistingO365Account
        );
        return view("link.index", $arrData);
    }

    /**
     * Create a new local account and link with O365 account.
     */
    public function createLocalAccount()
    {
        $user = Auth::user();
        if ($input = Input::all()) {
            $favoriteColor = $input['FavoriteColor'];
            $o365UserId = $user->o365UserId;
            $o365Email = $user->o365Email;
            $user = Auth::user();
            $user = $this->userServices->create($o365UserId, $o365Email, $user->firstName, $user->lastName,
                $user->OrganizationId, $favoriteColor, $o365Email);
            Auth::loginUsingId($user->id);
            SocializeAuthMiddleware::removeSocializeSessions();
            return redirect('/schools');
        } else {
            return view("link.createlocalaccount");
        }
    }

    /**
     * If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
     * If there's no local user with same email as o365 email on db, create a new user and then link.
     */
    public function loginLocal()
    {
        $localUser = Auth::user();
        if (!$localUser) {
            return redirect('/login');
        }
        $o365email = $localUser->o365Email;
        if ($input = Input::all()) {
            //Post from page. Link o365 user to an existing local account.
            $email = $input['email'];
            $password = $input['password'];
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $this->userServices->saveCurrentLoginUserInfo($localUser->o365UserId, $o365email, $localUser->firstName,
                    $localUser->lastName, $localUser->o365UserId);
                Auth::loginUsingId($user->id);
                SocializeAuthMiddleware::removeSocializeSessions();
                if (Auth::check()) {
                    return redirect("/schools");
                }
            } else {
                return back()->with('msg', 'Invalid login attempt.');
            }

        } else {
            //If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
            $user = $this->userServices->getUserByEmail($o365email);
            if ($user) {
                $this->userServices->saveUserInfoByEmail($localUser->o365UserId, $o365email, $localUser->firstName,
                    $localUser->lastName, $localUser->o365UserId);
                Auth::loginUsingId($user->id);
                SocializeAuthMiddleware::removeSocializeSessions();
                if (Auth::check()) {
                    return redirect("/schools");
                }

            }
            return view('link.loginlocal');
        }


    }

    public function loginO365Required()
    {
        return view('link.loginO365Required');
    }
}
