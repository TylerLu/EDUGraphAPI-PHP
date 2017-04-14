<?php

namespace App\Http\Middleware;

use App\Config\SiteConstants;
use Closure;
use Illuminate\Support\Facades\Auth;

class LocalOrO365LoginRequiredMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::check() && !isset($_SESSION[SiteConstants::Session_O365_User_ID]))
            return redirect("/login");
        return $next($request);
    }
}
