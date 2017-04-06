<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Link Office 365 & Local Account')
@section('content')
    @if(session('msg'))
        <div class="message-container bg-danger"> <p>{{session('msg')}}</p>  </div>
    @endif
    <h2>Link Office 365 & Local Account</h2>
    @if ($areAccountsLinked)
        <div>
            <p>Your local account and Office 365 account are now linked.</p>
            <p>
            </p><ul>
                <li>Local account: {{$localUserEmail}}</li>
                <li>Office 365 account: {{$o365UserEmail}}</li>
            </ul>
            <p></p>
            <p><a class="btn btn-default" href="/">Return to home</a></p>
        </div>
    @else
        <p>This page will enable you to link your Office 365 &amp; Local Account together to successfully use the demo application.</p>
        <hr>
        <div class="form-horizontal">
            @if($showLinkToExistingO365Account)
                <p>
                    <a class="btn btn-primary" href="{{url("/o365login")}}">Link to existing O365 account</a>
                </p>
            @else
                @if($isLocalUserExists)
                    <p>There is a local account: {{$localUserEmail}} matching your O365 account.</p>
                @endif
                <p>
                    @if($isLocalUserExists)
                        <a class="btn btn-primary" disabled="disabled" href="javascript:void(0)">Continue with new Local Account</a>
                    @else
                        <a class="btn btn-primary" href="{{url("/link/createlocalaccount")}}">Continue with new Local Account</a>
                    @endif
                    &nbsp; &nbsp;

                    <a class="btn btn-primary" href="{{url("/link/loginlocal")}}">Link with existing Local Account</a> &nbsp; &nbsp;
                </p>
            @endif

        </div>
    @endif

@endsection