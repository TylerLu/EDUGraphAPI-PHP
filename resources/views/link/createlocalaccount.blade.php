<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Create new local account')
@section('content')
    <h4>Link to your local account - Create new local account</h4>
    <hr>
    <section id="createLocalAccount">
        <form action="" class="form-horizontal" method="post" >
        {{csrf_field()}}
           <div class="form-group">
                <label class="col-md-2 control-label" for="FavoriteColor">Favorite color</label>
                <div class="col-md-10">
                    <select name="FavoriteColor" id="FavoriteColor" class="form-control">
                        <option value="#2F19FF">Blue</option>
                        <option value="#127605">Green</option>
                        <option value="#535353">Grey</option>
                    </select>

                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <input type="submit" value="Create and Link" class="btn btn-primary">
                </div>
            </div>
        </form></section>
@endsection