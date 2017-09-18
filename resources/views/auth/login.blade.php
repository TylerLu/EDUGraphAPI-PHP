<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Log in')
@section('content')
    <link href="{{asset('/public/css/login.css')}}" rel="stylesheet">

<div class="container body-content">
    <div class="loginbody">
    <div class="row">
        <div class="col-md-5 ">
            <div >
                <h4 class="margin-btm-20">Use your local account to log in</h4>
                <div class="validation-summary-errors text-danger">
                @if ($errors->has('email'))
                    <ul>
                        <li>{{ $errors->first('email') }}</li>
                    </ul>
                @endif
                @if ($errors->has('password'))
                        <ul>
                        <li>{{ $errors->first('password') }}</li>
                        </ul>
                @endif
                </div>
                <div >
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">


                            <div class="col-md-12">
                                <input id="email" type="email" class="form-control logincontrol" name="email" value="{{ old('email') }}" required autofocus placeholder="Email">


                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">


                            <div class="col-md-12">
                                <input id="password" type="password" class="form-control logincontrol" name="password" required placeholder="Password">


                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">

                                <input type="submit" value="Sign in" class="btn btn-default btn-local-login">

                                {{--<a class="btn btn-link" href="{{ route('password.request') }}">--}}
                                    {{--Forgot Your Password?--}}
                                {{--</a>--}}
                            </div>
                        </div>

                        <p>
                            <a class="registerlink" href="{{url("/register")}}">Register as a new user</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5 ">
            <h4 class="margin-btm-20">Use your school account</h4>
            <div id="socialLoginList">
                <p>
                   <a href="{{url('/o365login ')}}"  >
                    <button type="button" class="btn btn-default btn-ms-login" id="OpenIdConnect" name="provider" value="OpenIdConnect" title="Log in using your Microsoft Work or school account"></button>
                   </a>
                </p>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
