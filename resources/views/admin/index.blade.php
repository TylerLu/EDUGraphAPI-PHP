<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Admin')
@section('content')
    @if(session('msg') || $msg)
        <div class="message-container bg-danger"> <p>{{session('msg') }} <?php echo $msg; ?></p>  </div>
    @endif
    @if( $successMsg)
        <div class="message-container bg-success"> <p> {{$successMsg}}</p>  </div>
    @endif
    <h2>Admin</h2>
    @if (!$IsAdminConsented)
    <div>
        <h3>Admin Consent</h3>
        <hr />

        <p>To use this application in this tenancy you must first provide Admin Consent. </p>
        <p>Please click the button below to proceed.</p>

        <div class="form-group">
            <form method="post" action="{{url('/admin/adminconsent')}}">
            {{csrf_field()}}
            <input type="submit" value="Consent" class="btn btn-primary" />
        </form>
        </div>

    </div>
    @else
       <p>Admin Consent has been applied.</p>
        <hr/>
       <p>In some cases, you need to re-apply Admin Consent. For example, after the permissions of the AAD application change.</p>
       <p>Please click the button below to proceed.</p>
       <div class="form-group">
       <form method="post" action="{{url('/admin/adminconsent')}}">
           {{csrf_field()}}
           <input type="submit" value="Admin Consent" class="btn btn-default" />
       </form>
       </div>
       <div class="form-group">
       <p>Please click the button below to cancel the admin consent.</p>
       <form method="post" action="{{url('/admin/adminunconsent')}}">
           {{csrf_field()}}
           <input type="submit" value="Admin Unconsent" class="btn btn-default" />
       </form>
           <br/>
       <p>Note: It will take a few minutes to effect.</p>
       </div>
        <hr/>
        <div class="form-group">
            <p>If you want to view your linked account or unlink accounts, please click the button below.</p>
            <p><a class="btn btn-default" href="/admin/linkedaccounts">Manage Linked Accounts</a></p>
        </div>
        <hr/>
        <div class="form-group">
            <p>Click the button below to enable access to all your tenant users.</p>
            <form method="post" action="{{url('/admin/enableuseraccess')}}">
                {{csrf_field()}}
                <input type="submit" value="Enable User Access" class="btn btn-default" />
            </form>
            <br/>
            <p>Note: The App will take a while to effect.</p>
        </div>
        <hr />
       <div>

           <p>In some cases, your supplier may make changes that require them to ask you to reset a login cache. Click the button below to reset the cache.</p>

           <p class="form-group">
           <form method="post" action="{{url('/admin/clearAdalCache')}}">
               {{csrf_field()}}
               <input type="submit" value="Clear Login Cache" class="btn btn-default" />
           </form>
           </p>

       </div>
       <hr />
       <p><a class="btn btn-default" href="/">Return to home</a></p>
    @endif
@endsection