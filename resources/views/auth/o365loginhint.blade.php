<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
    <link href="{{asset('/public/css/login.css')}}" rel="stylesheet">

    <div class="loginbody logino365">
        <h3>Sign in</h3>
        <div class="headertext">{{$userName}}</div>
        <div class="bodytext">{{$email}}</div>
        <div class="bodytext">Office 365 account</div>
        <div class="formstyle">

            <div id="socialLoginList">
                <p>
<a href="{{url('/o365login ')}}"  >
    <button type="submit" class="btn btn-default btn-ms-login" id="" name="provider" value="{{$email}}" title="Log in using your {{$email}}"></button>
</a>
                </p>
            </div>

        </div>
        <div class="link">
            <a href="/differentaccount">Use a different account</a>
        </div>
    </div>

@endsection
