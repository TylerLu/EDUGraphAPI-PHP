<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Classes')
@section('content')
    <?php
            use App\Config\Roles;
    ?>

    <div class="row schools sections">
        <div class="tophero">
            <div class="col-md-8">
                <div class="secondnav">
                    <a href="/schools"> All Schools</a> > {{$school->displayName}}
                </div>
                <div class="a-heading">Classes</div>
            </div>
            <div class="toptiles">
                <div class="section-school-name">{{$school->displayName}}</div>
                <div class="infocontainer">
                    <div class="infoheader">PRINCIPAL</div>
                    <div class="infobody" title="{{$school->principalName}}">
                        @if($school->principalName)
                            {{$school->principalName}}
                        @else
                            -
                        @endif

                    </div>
                </div>
                <div class="infocontainer">
                    <div class="infoheader">Grade levels</div>
                    <div class="infobody" title="{{$school->lowestGrade}}-{{$school->highestGrade}}">
                        {{$school->lowestGrade}} - {{$school->highestGrade}}
                    </div>
                </div>
            </div>
            <div>
                <div class="col-md-4 usericon">
                    <div class="icon"></div>
                    @if($me->userRole === "Student" )
                        <div>Not Enrolled</div>
                    @else
                        <div>Not Teaching</div>
                    @endif
                    <div class="icon my-class"></div>
                    <div>My Class</div>
                </div>
                <div class="col-md-3 filterlink-container">
                    <div class="search-container "></div>
                    <span>FILTER:</span> <a id="filtermyclasses" class="filterlink selected" data-type="myclasses">My
                        Classes</a> |
                    <a id="filterclasses" class="filterlink " data-type="allclasses">All Classes</a>
                </div>
            </div>
            <br style="clear:both;"/>
        </div>
        <div class="myclasses-container tiles-root-container">
            <div id="allclasses" class="tiles-secondary-container">
                <div class="section-tiles">
                    @if(count($allClasses->value)==0)
                        <div class="nodata"> No classes in this school.</div>
                    @else
                        <div class="content clearfix">
                            @foreach($allClasses->value as $class)
                                <div class="tile-container">
                                    @if($class->isMySection)
                                        <a class="mysectionlink"
                                           href="{{url('/class/'.$school->id.'/'.$class->id)}}">
                                            @endif
                                            <div class="tile">
                                                <h5>{{$class->displayName}}</h5>
                                                <h2>{{$class->classCode}}</h2>
                                            </div>
                                            @if($class->isMySection)
                                        </a>
                                    @endif
                                    <div class="detail">
                                        <h5>Class Number:</h5>
                                        <h6>{{$class->classCode}}</h6>
                                        <h5>Teachers:</h5>
                                        @foreach($class->getTeachers() as $user)
                                            <h6>{{$user->displayName}}</h6>
                                        @endforeach
                                        <h5>Term Name:</h5>
                                        <h6>{{$class->term["displayName"]}}</h6>
                                        <h5>Start/Finish Date:</h5>
                                        <h6>
                                            <span id="termdate">{{$class->term["startDate"] ? (new DateTime($class->term["startDate"]))->format("c") : ""}}</span>
                                            <span> - </span>
                                            <span id="termdate">{{$class->term["endDate"] ? (new DateTime($class->term["endDate"]))->format("c") : ""}}</span>
                                        </h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($allClasses->skipToken)
                            <div class="seemore " id="see-more">
                                <input id="skiptoken" type="hidden" value="{{$allClasses->skipToken}}"/>
                                <input id="schoolid" type="hidden" value="{{$school->id}}"/>
                                <span>See More</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div id="myclasses" class="tiles-secondary-container">
                <div class="section-tiles">
                    @if(count($myClasses)===0)
                        @if($me->userRole === Roles::Faculty)
                            <div class="nodata"> Not teaching any classes.</div>
                        @else
                            <div class="nodata"> Not enrolled in any classes.</div>
                        @endif
                    @else
                        <div class="content clearfix">
                            @foreach($myClasses as $myClass)
                                <div class="tile-container">
                                    <a class="mysectionlink"
                                       href="{{url('/class/'.$school->id.'/'.$myClass->id)}}">
                                        <div class="tile">
                                            <h5>{{$myClass->displayName}}</h5>
                                            <h2>{{$myClass->classCode}}</h2>
                                        </div>
                                    </a>
                                    <div class="detail">
                                        <h5>Class Number:</h5>
                                        <h6>{{$myClass->classCode}}</h6>
                                        <h5>Teachers:</h5>

                                        @foreach($myClass->getTeachers() as $user)
                                                <h6>{{$user->displayName}}</h6>
                                        @endforeach
                                        <h5>Term Name:</h5>
                                        <h6>{{$myClass->term["displayName"]}}</h6>
                                        <h5>Start/Finish Date:</h5>
                                        <h6>
                                                <span id="termdate">{{$myClass->term["startDate"] ? (new DateTime($myClass->term["startDate"]))->format("c") : ""}}</span>
                                            <span> - </span>
                                            <span id="termdate">{{$myClass->term["endDate"] ? (new DateTime($myClass->term["endDate"]))->format("c") : ""}}</span>
                                        </h6>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('/public/js/moment.min.js') }}"></script>
    <script src="{{ asset('/public/js/sections.js') }}"></script>
@endsection
