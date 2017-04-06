<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Unlink Accounts')
@section('content')
    <div class="container body-content">
        <h2>Unlink Accounts</h2>
        <h3>Are you sure you want to unlink the accounts?</h3>
        <div>
            <hr>
            <dl class="dl-horizontal"></dl>
            <form action="/admin/dounlink/{{$user->id}}" method="post">
                {{csrf_field()}}
                <p>Local Account: {{$user->email}}</p>
                <p>Office 365 Account: {{$user->o365Email}}</p>
                <div class="form-actions no-color">
                    <input type="submit" value="Unlink" class="btn btn-default"> |
                    <a href="/admin/linkedaccounts">Back to List</a>
                </div>
            </form>
        </div>
        <br/>
    </div>
@endsection