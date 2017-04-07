<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'About Me')
@section('content')
    <div class="container ">
        <h2>About Me</h2>
        <div class="margin-top-12 margin-btm-12 aboutme">
            <b>Username:</b><br />
            {{$displayName}}
            @if($o365UserId)
                <img src="/userPhoto/{{$o365UserId}}"  />
             @endif
        </div>
        <div class="margin-top-12 margin-btm-12">

            <b>Logged in as:</b><br /> {{$role}}
        </div>

            <div class="margin-btm-12">
                @if($favoriteColor)
                    <b>Favorite Color:</b>
                <br/>
                <form method="post" action="/auth/savefavoritecolor">
                    {{ csrf_field() }}
                    <select name="FavoriteColor" id="FavoriteColor"  aria-invalid="false">
                        <option value="#2F19FF" {{$favoriteColor==='#2F19FF'?'selected':''}}>Blue</option>
                        <option value="#127605" {{$favoriteColor==='#127605'?'selected':''}}>Green</option>
                        <option value="#535353" {{$favoriteColor==='#535353'?'selected':''}}>Grey</option>
                    </select>
                    <input type="submit" value="Save">
                    @if($showSaveMessage)
                        <span class="saveresult">Favorite color has been updated!</span>
                    @endif
                </form>
                @endif


            </div>

            <div class="margin-btm-12 ">
                <b>Classes:</b>
                <br />
                <div>
                    @if(count($classes)>0)
                         @foreach($classes as $class)
                            {{$class->displayName}}
                            <br/>
                        @endforeach
                    @endif
                </div>
            </div>
    </div>
@endsection
