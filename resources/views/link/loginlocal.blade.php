<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Log in with your local account')
@section('content')
    <h4>Link to your local account - Log in with your local account:</h4>

    <section id="createLocalAccount">
        @if(session('msg'))
            <ul><li>{{session('msg')}}</li></ul>
        @endif
        <form action="" class="form-horizontal" method="post" >
            {{csrf_field()}}
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                <label class="col-md-2 control-label" for="Email">Email</label>
                <div class="col-md-10">

                    <input id="email" type="email" class="form-control logincontrol" name="email" value="{{ old('email') }}" required autofocus >

                    @if ($errors->has('email'))
                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label class="col-md-2 control-label" for="Password">Password</label>

                <div class="col-md-10">
                    <input id="password" type="password" class="form-control logincontrol" name="password" required >

                    @if ($errors->has('password'))
                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <input type="submit" value="Log in and Link" class="btn btn-default">
                </div>
            </div>
        </form>
    </section>
@endsection