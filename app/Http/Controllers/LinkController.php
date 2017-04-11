<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;

use App\Config\SiteConstants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Model\TokenCache;
use App\Services\TokenCacheService;
use App\Services\AADGraphClient;


class LinkController extends Controller
{
    public  function index()
    {

        $isLocalUserExists = false;
        $areAccountsLinked = false;
        $showLinkToExistingO365Account = false;
        $localUserEmail = '';

        //If session exists for O365 user, it's the first time that an O365 user login.
        if(isset($_SESSION[SiteConstants::Session_O365_User_ID])){
            $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
            if($o365userId){
                $user  = User::where('email', $_SESSION[SiteConstants::Session_O365_User_Email])->first();
                if($user){
                    $isLocalUserExists = true;
                    $localUserEmail = $_SESSION[SiteConstants::Session_O365_User_Email];
                }
                (new AADGraphClient)->GetCurrentUserAndUpdateUserRoles($o365userId);
            }
        }

        //check if a user is login.
        $o365UserEmailInDB='';
        if (Auth::check()) {
            $user = Auth::user();
            $o365UserIdInDB= $user->o365UserId;
            $o365UserEmailInDB=$user->o365Email;
            $localUserEmail = $user->email;
            if( !$o365UserEmailInDB || ! $o365UserIdInDB || $o365UserEmailInDB==='' || $o365UserIdInDB==='') {
                //Local user login but not linked. Should show link to existing o365 account link and then login to o365.
                $showLinkToExistingO365Account = true;
            }else{
                $areAccountsLinked = true;
            }
        }
        $arrData = array(
            'isLocalUserExists'=>$isLocalUserExists,
            'areAccountsLinked'=>$areAccountsLinked,
            'localUserEmail' =>$localUserEmail,
            'o365UserEmail'=>$o365UserEmailInDB,
            'showLinkToExistingO365Account' =>$showLinkToExistingO365Account
        );
        return view("link.index", $arrData);
    }

    /**
     * Create a new local account and link with O365 account.
     */
    public function createLocalAccount()
    {

        if($input =Input::all()){
            $favoriteColor = $input['FavoriteColor'];
            $o365UserId = $_SESSION[SiteConstants::Session_O365_User_ID];
            $o365Email = $_SESSION[SiteConstants::Session_O365_User_Email];

            $user = new User();
            $user->o365UserId=$o365UserId;
            $user->o365Email=$o365Email;
            $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
            $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
            $user->password = '';
            $user->OrganizationId =$_SESSION[SiteConstants::Session_OrganizationId];
            $user->favorite_color   =$favoriteColor;
            $user->email=$o365Email;
            $user->save();
            Auth::loginUsingId($user->id);
            (new TokenCacheService)->UpdateOrInsertCache($o365UserId,$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
            return redirect('/schools');
        }else{
            return view("link.createlocalaccount");
        }
    }

    /**
     * If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
     * If there's no local user with same email as o365 email on db, create a new user and then link.
     */
    public function loginLocal()
    {
        $o365email = $_SESSION[SiteConstants::Session_O365_User_Email];
        if($input =Input::all()){
            //Post from page. Link o365 user to an existing local account.
           $email = $input['email'];
           $password = $input['password'];
           $credentials = [
                'email' => $email,
                'password' => $password,
            ];
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->password = '';
                $user->OrganizationId =$_SESSION[SiteConstants::Session_OrganizationId];
                $user->save();
                Auth::loginUsingId($user->id);

                (new TokenCacheService)->UpdateOrInsertCache($_SESSION[SiteConstants::Session_O365_User_ID],$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }else{
                return back()->with('msg','Invalid login attempt.');
            }

        }
        else{
            //If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
            $user  = User::where('email', $o365email)->first();

            if($user){
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->password = '';
                $user->OrganizationId =$_SESSION[SiteConstants::Session_OrganizationId];
                $user->save();
                Auth::loginUsingId($user->id);
                (new TokenCacheService)->UpdateOrInsertCache($_SESSION[SiteConstants::Session_O365_User_ID],$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
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
