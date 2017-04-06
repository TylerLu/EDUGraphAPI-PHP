<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Login to Office 365 is required')
@section('content')
    <h2>Login to Office 365 is required</h2>
    <p>You were redirected to this page for one of the following reasons:</p>
    <p>
    <ul>
        <li>The app could not find your token.</li>
        <li>Your token has expired.</li>
    </ul>
    </p>
    <p>To continue, please click the button below to login with your Office 365 account.</p>
    <p>
        <a href="{{url('/o365login ')}}"  >
            <button type="button" class="btn btn-default btn-ms-login" id="OpenIdConnect" name="provider" value="OpenIdConnect" title="Login with Office 365 account">Login with Office 365 account</button>
        </a>

    </p>
@endsection