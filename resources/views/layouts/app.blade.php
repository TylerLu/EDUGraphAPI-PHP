<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

use App\Config\Roles;
use App\Config\SiteConstants;
use App\Services\UserRolesService;use Illuminate\Http\Request;use Illuminate\Support\Facades\Input;use Illuminate\Support\Facades\Route;
?>

<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('/public/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/public/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/public/css/site.css') }}" rel="stylesheet">
    <!-- Scripts -->
    <script src="{{ asset('/public/js/app.js') }}"></script>
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });
    </script>
</head>
<body>
    <div id="app" class="navbar navbar-inverse navbar-fixed-top">
        <nav >
            <div class="container topnavcontainer">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>
                <?php

                $role='';
                $o365userId='';
                if(Auth::user())
                    $o365userId=Auth::user()->o365UserId;

                if($o365userId)
                    $role = (new UserRolesService)->getUserRole($o365userId);

                $isInASchool=false;
                $objectId='';
                $schoolId='';
                $route = Route::current();
                if(isset($route->parameters['objectId']))
                    $objectId = $route->parameters['objectId'];
                $actionName = $route->getActionName();

                if($actionName ==='App\Http\Controllers\SchoolsController@users' || $actionName==='App\Http\Controllers\SchoolsController@classes'
                || $actionName==='App\Http\Controllers\SchoolsController@classDetail' ){
                     $isInASchool=true;
                }
                ?>
                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <li><a href="{{ url('/') }}">Home</a></li>

                    <?php

                    if($isInASchool && $objectId){
                    ?>
                        <li><a href="{{ url('/classes/'.$objectId) }}">Classes</a></li>
                        <li><a href="{{ url('/users/'.$objectId) }}">Teachers & Students</a></li>
                    <?php
                      }
                        if($role && $role==Roles::Admin){
                        ?>
                        <li><a href="{{ url('admin') }}">Admin</a></li>
                     <?php
                            }
                        ?>

                    </ul>
                    <!-- Right Side Of Navbar -->

                    <ul class="nav navbar-nav navbar-right">
                        @yield('registerarea')
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle aboutme" data-toggle="dropdown" role="button" aria-expanded="false">
                                    <?php

                                    if($role)
                                        {
                                            if($role ===Roles::Faculty)
                                                $role='Teacher';
                                            $msg='Logged in as: '.$role .'. ';
                                            echo $msg;
                                        }
                                    if(Auth::user()){
                                        $displayName = Auth::user()->email;
                                        if(Auth::user()->firstName !='')
                                            $displayName =Auth::user()->firstName .' '. Auth::user()->lastName;
                                        echo 'Hello ' . $displayName;
                                    }

                                    if($o365userId){
                                        echo '<img src="/userPhoto/'.$o365userId.'"  />';
                                    }
                                    ?>


                                   <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="/auth/aboutme">About Me</a></li>
                                    <li><a href="/link">Link</a></li>
                                    <li>
                                        <a href="{{ url('/userlogout') }}">
                                            Log off
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>

                    </ul>

                </div>
            </div>
        </nav>


    </div>

        <div class="containerbg">
            <div class="container body-content">
                @yield('content')
            </div>
    </div>
    <?php include 'resources/views/demohelper.php';?>
    <!-- Scripts -->
    <script src="{{ asset('/public/js/site.js') }}"></script>
</body>
</html>
