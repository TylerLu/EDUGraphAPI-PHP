<?php

namespace App\Http\Controllers\Auth;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{

    public function Logout()
    {
        Session::flush();
        $_SESSION=array();
        session_destroy();
        Auth::logout();
        $redirectURl=urlencode('http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST']);
        $url = 'https://login.microsoftonline.com/common/oauth2/logout?post_logout_redirect_uri='.$redirectURl;
        return Redirect::to($url);
    }
}
