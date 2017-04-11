<?php
/**
*  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
*  See LICENSE in the project root for license information.
*/
?>

@extends('layouts.app')
@section('title', 'Admin Consent')
@section('registerarea')
    <?php
    if(!Auth::user()){
    ?>
    <style>
        .navbar-right li.dropdown{
            display: none;}
    </style>
    <ul class="nav navbar-nav">
        <li><a href="{{url('register')}}" id="registerLink">Register</a></li>
        <li><a href="{{url('login')}}" id="loginLink">Log in</a></li>
    </ul>
    <?php
    }
    ?>
 @endsection
@section('content')
    @if(session('msg') || $msg)
        <div class="message-container bg-success"> <p>{{session('msg') }} <?php echo $msg; ?></p>  </div>
    @endif

@if(!$consented)
    <div>
        <h3>Admin Consent</h3>
        <hr />

        <p>To use this application in this tenancy you must first provide Admin Consent. </p>
        <p>Please click the button below to proceed.</p>

        <p class="form-group">
        <form method="post" action="{{url('/admin/adminconsent')}}">
            {{csrf_field()}}
            <input type="submit" value="Consent" class="btn btn-primary" />
        </form>
        </p>

    </div>
    @else
    <hr/>
    <p>Admin Consent has been applied.</p>
    @endif



@endsection