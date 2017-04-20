<?php

namespace App\Http\Middleware;

use App\Config\SiteConstants;
use App\Config\UserType;
use App\Services\UserService;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class SocializeAuthMiddleware
{
    /**
     * Integrate O365 user with PHP authentication framework. The current O365 user could be got through Auth:user().
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()) {
            if (isset($_SESSION[SiteConstants::SocializeUser])) {
                $userInfo = $_SESSION[SiteConstants::SocializeUser];
                $jsonUser = json_decode(json_encode($userInfo['user']));
                $user = new User();
                $user->userType = UserType::O365;
                $user->firstName =$jsonUser->user->givenName;
                $user->lastName = $jsonUser->user->surname;
                $user->o365UserId = $jsonUser->id;
                $user->o365Email = $jsonUser->email;
                $user->OrganizationId = $userInfo['organizationId'];
                $user->tenantId = $userInfo['tenantId'];
                Auth::login($user);
            }
        }
        return $next($request);
    }

    public static function removeSocializeSessions()
    {
        $_SESSION[SiteConstants::SocializeUser]=null;
    }

    public static function setSocializeSessions($user,$orgId,$tenantId)
    {
        $arrData = array(
            'user'=>$user,
            'organizationId'=>$orgId,
            'tenantId' =>$tenantId
        );
        $_SESSION[SiteConstants::SocializeUser] = $arrData;
    }
}
