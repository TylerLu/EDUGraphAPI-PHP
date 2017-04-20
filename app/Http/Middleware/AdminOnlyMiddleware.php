<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Middleware;

use App\Services\UserRolesService;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminOnlyMiddleware
{
    /**
     * Only allows admin to access the protected routes. It is mainly used for AdminController.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $isAdmin = (new UserRolesService)->IsUserAdmin($user->o365UserId);
        if (!$isAdmin)
            return redirect("/login");
        return $next($request);
    }
}
