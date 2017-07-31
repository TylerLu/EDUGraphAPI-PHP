<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Class Details')
@section('content')
    <div class="row schools class-details">
        <div class="tophero">
            <div class="container">
                <div class="col-md-6 secondnav">
                    <a href="/schools"> All Schools</a>
                    > <a href="/classes/{{$school->id}}">{{$school->displayName}}</a>
                    > {{$section->courseName}}
                </div>
            </div>
            <div class="container">
                <div class="a-heading ">Class Details</div>
                <div class="b-title">{{$section->courseNumber . " | " . $section->displayName}}</div>
            </div>
            <div class="container coursedetail">
                <div class="col-md-6">
                    <span>Course Name:</span> {{$section->courseName}}
                    <br/>
                    <span>Description:</span> {{$section->courseDescription}}
                    <br/>
                    <span>Period:</span> {{$section->period}}
                    <br/>
                    <span>Term Name:</span> {{$section->termName}}
                    <br/>
                    <span>Start/Finish Date:</span>
                    <span id="termdate">{{$section->termStartDate ? (new DateTime($section->termStartDate))->format("c") : ""}}</span>
                    <span id="termdate-separator"> - </span>
                    <span id="termdate">{{$section->termEndDate ? (new DateTime($section->termEndDate))->format("c") : ""}}</span>
                </div>
                <div class="col-md-6">
                    <span>Teacher:</span>
                    <div style="display:inline-block">
                        @foreach ($section->getTeachers() as $teacher)
                            {{$teacher->displayName}}
                            @if(!$loop->last)
                                <br/>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="students">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#students"><span>Students</span></a></li>
                <li><a data-toggle="tab" href="#documents" id="classdoclink"><span>Class Documents</span></a></li>
                <li><a data-toggle="tab" href="#conversations"><span>Conversations</span></a></li>
                <li><a data-toggle="tab" href="#seatingchart"><span>SEATING CHART</span></a></li>
            </ul>
            <div class="tab-content">
                <div id="students" class="tab-pane fade in active">
                    @if (is_null($section) || empty($section->getStudents()))
                        <div class="nodata"> There is no data available for this page at this time.</div>
                    @else
                        <table class="table  table-green table-student tablesorter" id="studentsTable">
                            <thead>
                            <tr class="table-green-header">
                                <th class="tdleft">student name</th>
                                <th>grade</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($section->getStudents() as $student)
                                <tr class="tr-content">
                                    <td><img src="../../public/images/header-default.jpg"
                                             realheader="/userPhoto/{{$student->o365UserId}}"/> {{$student->displayName}}
                                    </td>
                                    <td>{{$student->educationGrade}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div id="documents" class="tab-pane fade">
                    @if (empty($driveItems))
                        <div class="nodata"> There is no data available for this page at this time.</div>
                    @else
                        <table id="studoc" class="table  table-green table-student tablesorter">
                            <thead>
                            <tr class="table-green-header">
                                <th class="border-holder"></th>
                                <th class="space-holder left"></th>
                                <th class="tdleft">document name</th>
                                <th class="tdleft">modified</th>
                                <th class="tdleft">modified by</th>
                                <th class="space-holder right"></th>
                            </tr>
                            <tr class="blanktr">
                                <th colspan="5"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($driveItems as $doc)
                                <tr class="tr-content">
                                    <td class="border-holder"></td>
                                    <td class="space-holder left"></td>
                                    <td>
                                        <a target="_blank" href="{{$doc->getWebUrl()}}">{{$doc->getName()}}</a>
                                    </td>
                                    <td>{{$doc->getLastModifiedDateTime() ? $doc->getLastModifiedDateTime()->format("c") : ""}}</td>
                                    <td>{{$doc->getLastModifiedBy()->getUser()->getDisplayName()}}</td>
                                    <td class="space-holder right"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                    <span class="see-more"><a href="{{$seeMoreFilesUrl}}" target="_blank">See All</a></span>
                    <br style="clear:both"/>
                </div>
                <div id="conversations" class="tab-pane fade">
                    @if (empty($conversations))
                        <div class="nodata"> There is no data available for this page at this time.</div>
                    @else
                        <table id="stuconv" class="table  table-green table-student">
                            <tbody>
                            @foreach ($conversations as $conversation)
                                <tr class="tr-content">
                                    <td class="border-holder"></td>
                                    <td class="space-holder left"></td>
                                    <td>
                                        <a target="_blank"
                                           href="{{$seeMoreConversationsUrl . '&ConvID=' . $conversation->getId()}}">{{$conversation->getTopic()}}</a>
                                    </td>
                                    <td class="space-holder right"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                    <span class="see-more"><a target="_blank" href="{{$seeMoreConversationsUrl}}">See All</a></span>
                    <br style="clear:both"/>
                </div>

                <div id="seatingchart" class="tab-pane fade ">
                    <div class="left" id="dvleft">
                        @if (!$isStudent)
                            <div class="tip"> To assign seats for each student, drag and drop student profile icons
                                below onto the seating map to the right.
                            </div>
                        @else
                            <div class="assignseat tip">
                                <div class="greenicon" style="background-color:{{$myFavoriteColor}}"></div>
                                Your assigned seat
                            </div>
                        @endif
                        <ul id="lstproducts">
                            @foreach ($section->getStudents() as $student)
                                <li id="{{$student->o365UserId}}"><img src="../../public/images/header-default.jpg"
                                                                       realheader="/userPhoto/{{$student->o365UserId}}"/>
                                    <span class="disname"> {{$student->displayName}} </span> <span
                                            class="seated {{$student->position === 0 ? 'hideitem' : ''}}">seated ✓</span>
                                </li>
                            @endforeach
                        </ul>
                        <div id="hidtiles" class="hideitem">
                            @foreach ($section->getStudents() as $student)
                                <div class="deskcontainer {{$student->position === 0 ? 'unsaved': 'white'}} {{empty($student->favoriteColor) ? '': 'green'}}"
                                     style="{{empty($student->favoriteColor) ? '': 'background-color:' . $student->favoriteColor}}"
                                     position="{{$student->position}}" userid="{{$student->o365UserId}}">
                                    <div class="deskclose"><img src="../../public/Images/close.png"></div>
                                    <div class="deskicon">
                                        <img src="/images/header-default.jpg"
                                             realheader="/userPhoto/{{$student->o365UserId}}"/>
                                    </div>
                                    <div class="stuname">{{$student->displayName}}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="right" id="dvright">
                        <div class="teacherdesk" style="{{$isStudent ? '' : 'background-color:' . $myFavoriteColor}}">
                            Teacher's desk
                        </div>
                        <div>
                            <div id="graybg">
                                @for ($i = 1; $i <= 36; $i++)
                                    <div class="desktile" position="{{$i}}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div id="dvedit" class="dvedit">
                        @if (!$isStudent)
                            <img id="imgedit" src="../../public/Images/edit.png"/>
                            <img id="imgsave" src="../../public/Images/save.png"/>
                            <img id="imgcancel" src="../../public/Images/cancel.png"/>
                        @endif
                    </div>
                    <br style="clear:both"/>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="hidSectionid" id="hidSectionid" value="{{$section->id}}"/>
    <script src="{{ asset('/public/js/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('/public/js/moment.min.js') }}"></script>
    <script src="{{ asset('/public/js/classdetail.js') }}"></script>
@endsection