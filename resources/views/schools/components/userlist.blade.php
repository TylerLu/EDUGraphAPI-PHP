<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@if(!$users || !$users->value || empty($users->value))
    <div class="nodata"> There is no data available for this page at this time.</div>
@else
    <div class="content">
        @foreach ($users->value as $user)
            <div class="element {{$user->educationObjectType == "Teacher" ? "teacher-bg" : "student-bg"}}">
                <div class="userimg">
                    <img src="../public/images/header-default.jpg" realheader="/userPhoto/{{$user->o365UserId}}"/>
                </div>
                <div class="username">{{$user->displayName}}</div>
            </div>
        @endforeach
    </div>
    @if ($users->skipToken)
        <div class="pagination">
            <input id="skipToken" type="hidden" value="{{$users->skipToken}}"/>
            <input id="curpage" type="hidden" value="1"/>
            <span class="current prev">Previous</span>
            <span class="next">Next</span>
        </div>
    @endif
@endif