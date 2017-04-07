<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Teachers & Students')
@section('content')
    <div class="row schools teacher-student">
        <div class="tophero">
            <div class="col-md-8">
                <div class="secondnav">
                    <a href="/schools">All Schools</a> > {{$school->displayName}}</div>
                <div class="a-heading">Teachers & Students</div>
            </div>
            <div class="toptiles">
                <div class="section-school-name">{{$school->displayName}}</div>
                <div class="infocontainer">
                    <div class="infoheader">PRINCIPAL</div>
                    <div class="infobody" title="{{$school->principalName}}">
                        {{$school->principalName}}
                        @if(!$school->principalName )
                            -
                        @endif
                    </div>
                </div>
                <div class="infocontainer">
                    <div class="infoheader">Grade levels</div>
                    <div class="infobody" title="{{$school->lowestGrade . ' - ' . $school->highestGrade}}">
                        {{$school->lowestGrade . ' - ' . $school->highestGrade}}
                    </div>
                </div>
            </div>
            <div>
                <div class="col-md-6 usericon">
                    <div class="stuicon"></div>
                    <div>Student</div>
                    <div class="teacicon"></div>
                    <div>Teacher</div>
                </div>
                <div class="col-md-6 filterlink-container">
                    <span>FILTER:</span> <a id="filterteacher" class="filterlink" data-type="teachers">Teachers</a> | <a
                            id="filterstudnet" class="filterlink" data-type="students">Students</a> | <a id="filterall"
                                                                                                         class="filterlink selected"
                                                                                                         data-type="users">All</a>
                </div>
            </div>
            <br style="clear:both;"/>
        </div>
        <div class="users-container tiles-root-container">
            <div id="users" class="tiles-secondary-container">
                @component("schools.components.userlist", ["users" => $users])
                @endcomponent
            </div>
            <div id="students" class="tiles-secondary-container">
                @component("schools.components.userlist", ["users" => $students])
                @endcomponent
            </div>
            <div id="teachers" class="tiles-secondary-container">
                @component("schools.components.userlist", ["users" => $teachers])
                @endcomponent
            </div>
        </div>
        <input id="school-objectid" type="hidden" value="{{$school->objectId}}"/>
    </div>
    <script src="{{ asset('/public/js/users.js') }}"></script>
@endsection
