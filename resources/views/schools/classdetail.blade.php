<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'Class Details')
@section('content')

    <link rel="stylesheet" type="text/css" href="/public/css/jquery-ui.css">

    <div class="row schools class-details">
        <div class="tophero">
            <div class="container">
                <div class="col-md-6 secondnav">
                    <a href="/schools"> All Schools</a>
                    > <a href="/classes/{{$school->id}}">{{$school->displayName}}</a>
                    > {{$section->displayName}}
                </div>
            </div>
            <div class="container">
                <div class="a-heading ">Class Details</div>
                <div class="b-title">{{$section->classCode . " | " . $section->displayName}}</div>
            </div>
            <div class="container coursedetail">
                <div class="col-md-6">

                    <span>Term Name:</span> {{$section->term["displayName"]}}
                    <br/>
                    <span>Start/Finish Date:</span>
                    <span id="termdate">{{$section->term["startDate"] ? (new DateTime($section->term["startDate"]))->format("c") : ""}}</span>
                    <span id="termdate-separator"> - </span>
                    <span id="termdate">{{$section->term["endDate"] ? (new DateTime($section->term["endDate"]))->format("c") : ""}}</span>
                </div>
                <div class="col-md-6">
                    <span>Teacher:</span>

                        @foreach ($section->getTeachers() as $teacher)

                        <span class="coteacher-name">
                            {{!$loop->last? $teacher->displayName . ', ':$teacher->displayName}}
                         </span>

                        @endforeach
                            @if (!$isStudent)
                                <a id="addateacher" href="javascript:void(0)">Add a teacher</a>
                            @endif
                </div>
                    @if (!$isStudent)
                        <div class="schoolteachers">
                            <div class="close"><img src="/Images/close.png"></div>
                            <div class="title">Select a teacher</div>
                            <div class="list">
                                @if(!isset($filteredTeachers) || empty($filteredTeachers))
                                    <div > There is no data available at this time.</div>
                                    @else
                                    <ul>
                                        @foreach ($filteredTeachers as $teacher)
                                            <li>
                                                <a href="/addCoTeacher/{{$section->id}}/{{$teacher->id}}">
                                                <img src="../../public/images/header-default.jpg"
                                                     realheader="/userPhoto/{{$teacher->id}}"/> {{$teacher->displayName}}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>
                        </div>
                    @endif

            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="students">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#students"><span>Students</span></a></li>
                <li><a data-toggle="tab" href="#assignments" id="assignmentslink"><span>Assignments</span></a></li>
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

                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($section->getStudents() as $student)
                                <tr class="tr-content">
                                    <td><img src="../../public/images/header-default.jpg"
                                             realheader="/userPhoto/{{$student->id}}"/> {{$student->displayName}}
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div id="assignments" class="tab-pane fade">
                    @if (!$isStudent)
                        <div class="addassignment"><a href="javascript:void(0)"> + New</a></div>
                    @endif
                        <div id="assignmentslist">
                            @if (is_null($assignments) || empty($assignments))
                                <div class="nodata"> There is no data available for this page at this time.</div>
                            @else
                                <table class="table  table-green table-student ">
                                    <thead>
                                    <tr class="table-green-header">
                                        <th class="header">Name</th>
                                        <th class="header">Due Date</th>
                                        <th class="header">Status</th>
                                        <th class="header">Details</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach ($assignments as $assignment)
                                        <tr class="tr-content">
                                            <td>{{$assignment->displayName}}</td>
                                            <td class="assignmentdate">
                                                {{$assignment->dueDateTime ? $assignment->dueDateTime : ""}}
                                            </td>
                                            <td>{{$assignment->status}}</td>
                                            <td>
                                                <a class="detaillink"
                                                   data-status="{{$assignment->status}}"
                                                   data-id="{{$assignment->id}}"
                                                   data-dueDate="{{$assignment->dueDateTime}}"
                                                   data-title="{{$assignment->displayName}}"
                                                   data-allowlate="{{$assignment->allowLateSubmissions}}"
                                                   href="javascript:void(0)">Details</a>
                                                @if (!$isStudent && $assignment->status != "draft")
                                                    <span>|</span>
                                                    <a href="javascript:void(0)" class="submissionslink"  data-status="{{$assignment->status}}" data-id="{{$assignment->id}}" data-dueDate="{{$assignment->dueDateTime ? (new DateTime($assignment->dueDateTime))->format("c") : ""}}" data-title="{{$assignment->displayName}}" > Submissions</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            @endif
                                @if (!$isStudent)
                                <div class="modal fade"id="new-assignment" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="alert alert-danger assignment-alert">
                                                <a href="#" class="close" data-dismiss="alert">&times;</a>
                                                <span></span>
                                            </div>
                                            <form action="/newAssignment" enctype="multipart/form-data" id="new-assignment-form" method="post">
                                            <div class="modal-body">

                                                <input id="FilesToBeUploaded" name="FilesToBeUploaded" type="hidden" value="">
                                                <input id="status" name="status" type="hidden" />
                                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                                <input name="schoolId" type="hidden" value="{{$school->id}}" />
                                                <input name="classId" type="hidden" value="{{$section->id}}" />


                                                <fieldset>
                                                    <label>Name</label>
                                                    <input type="text" name="name" id="name" value="" class="text ui-widget-content ui-corner-all">
                                                    <br />
                                                    <label>Due Date</label>
                                                    <input type="text" id="duedate" name="duedate" value="" class="text ui-widget-content ui-corner-all">
                                                    <select class="ui-widget-content ui-corner-all" id="duetime" name="duetime">
                                                        <option>12:00 AM</option>
                                                        <option>12:30 AM</option>
                                                        <option>1:00 AM</option>
                                                        <option>1:30 AM</option>
                                                        <option>2:00 AM</option>
                                                        <option>2:30 AM</option>
                                                        <option>3:00 AM</option>
                                                        <option>3:30 AM</option>
                                                        <option>4:00 AM</option>
                                                        <option>4:30 AM</option>
                                                        <option>5:00 AM</option>
                                                        <option>5:30 AM</option>
                                                        <option>6:00 AM</option>
                                                        <option>6:30 AM</option>
                                                        <option>7:00 AM</option>
                                                        <option>7:30 AM</option>
                                                        <option>8:00 AM</option>
                                                        <option>8:30 AM</option>
                                                        <option>9:00 AM</option>
                                                        <option>9:30 AM</option>
                                                        <option>10:00 AM</option>
                                                        <option>10:30 AM</option>
                                                        <option>11:00 AM</option>
                                                        <option>11:30 AM</option>
                                                        <option>12:00 PM</option>
                                                        <option>12:30 PM</option>
                                                        <option>1:00 PM</option>
                                                        <option>1:30 PM</option>
                                                        <option>2:00 PM</option>
                                                        <option>2:30 PM</option>
                                                        <option>3:00 PM</option>
                                                        <option>3:30 PM</option>
                                                        <option>4:00 PM</option>
                                                        <option>4:30 PM</option>
                                                        <option>5:00 PM</option>
                                                        <option>5:30 PM</option>
                                                        <option>6:00 PM</option>
                                                        <option>6:30 PM</option>
                                                        <option>7:00 PM</option>
                                                        <option>7:30 PM</option>
                                                        <option>8:00 PM</option>
                                                        <option>8:30 PM</option>
                                                        <option>9:00 PM</option>
                                                        <option>9:30 PM</option>
                                                        <option>10:00 PM</option>
                                                        <option>10:30 PM</option>
                                                        <option>11:00 PM</option>
                                                        <option>11:30 PM</option>
                                                        <option>11:59 PM</option>
                                                    </select>
                                                    <br />
                                                    <div class="uploadcontainer">
                                                        <label>Resources</label>
                                                        <div id="uploaders">
                                                            <input type="file" id="fileToUpload" name="fileUpload[]" />
                                                        </div>
                                                    </div>
                                                    <br />
                                                    <span id="spnFile" style="color: #FF0000"></span>
                                                    <div class="control-section" style="padding: 0px;">
                                                        <div id="selectedFiles"></div>
                                                    </div>
                                                </fieldset>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-cancel" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-primary btn-save">Save As Draft</button>
                                                <button type="button" class="btn btn-primary btn-publish">Assign</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                                <div class="modal fade assignment-detail-modal" id="assignment-detail-form" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="alert alert-danger assignment-alert">
                                                <a href="#" class="close" data-dismiss="alert">&times;</a>
                                                <span></span>
                                            </div>
                                            @if (!$isStudent)
                                                <form action="/updateAssignment" enctype="multipart/form-data" id="assignment-detail-form-teacher" method="post">                                    <input name="schoolId" type="hidden" value="402aedc5-3189-451a-a2bd-ac294560326f">
                                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                                <input name="schoolId" type="hidden" value="{{$school->id}}" />
                                                <input name="classId" type="hidden" value="{{$section->id}}" />
                                                <input name="assignmentId" type="hidden" />
                                                <input name="assignmentStatus" type="hidden" />

                                                <div class="modal-body">
                                                    <div><h5 class="assignment-title"></h5></div>
                                                    <div><h5 class="due-date"></h5></div>

                                                    <div class="row resource-upload">
                                                        <h5 class="resources-title col-md-8"></h5>
                                                        <button type="button" class="btn btn-primary btn-new">+ New</button>
                                                        <input type="file" id="newResourceFileCtrl" name="newResource[]" class="hidden">
                                                    </div>

                                                    <ul class="resource-list"></ul>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-cancel" data-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-primary btn-save">Save</button>
                                                    <button type="button" class="btn btn-primary btn-publish">Assign</button>
                                                    <button type="submit" class="btn btn-primary btn-update">Update</button>
                                                </div>
                                                </form>
                                               @else
                                                <form action="/newAssignmentSubmissionResource" enctype="multipart/form-data" id="assignment-detail-form-student" method="post">
                                                <input name="schoolId" type="hidden" value="{{$school->id}}" />
                                                <input name="classId" type="hidden" value="{{$section->id}}" />
                                                <input name="assignmentId" type="hidden" />
                                                <input name="submissionId" type="hidden" />
                                                  <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />



                                                <div class="modal-body">
                                                    <div><h5 class="assignment-title"></h5></div>
                                                    <div><h5 class="due-date"></h5></div>
                                                    <div><h5 class="allow-late"></h5></div>
                                                    <div class="row">
                                                        <h5 class="resources-title col-md-8"></h5>
                                                    </div>
                                                    <ul class="resource-list"></ul>

                                                    <div class="row resource-upload">
                                                        <h5 class="handin-title col-md-8"></h5>
                                                        <button type="button" class="btn btn-primary btn-upload">Upload</button>

                                                        <input type="file" id="newResourceFileCtrl" name="newResource[]" class="hidden">


                                                    </div>
                                                    <ul class="handin-list"></ul>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-cancel" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                                                </div>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade assignment-detail-modal" id="assignment-submissions-form" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div><h3>Assignment Submissions</h3></div>
                                                <div><h5 class="assignment-title"></h5></div>
                                                <div><h5 class="due-date"></h5></div>
                                                <div class="row">
                                                    <table class="table resource-list" id="assignment-submissions-table">
                                                        <thead>
                                                        <tr>
                                                            <th>Submitted by</th>
                                                            <th>Submitted On</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary btn-update" data-dismiss="modal">Return to Assignments</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>

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
                    <span class="see-more"><a href="{{$seeMoreFilesUrl}}" target="_blank">See More</a></span>
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
                    <span class="see-more"><a target="_blank" href="{{$seeMoreConversationsUrl}}">See More</a></span>
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
                                <li id="{{$student->id}}"><img src="../../public/images/header-default.jpg"
                                                                       realheader="/userPhoto/{{$student->id}}"/>
                                    <span class="disname"> {{$student->displayName}} </span> <span
                                            class="seated {{$student->position === 0 ? 'hideitem' : ''}}">seated ✓</span>
                                </li>
                            @endforeach
                        </ul>
                        <div id="hidtiles" class="hideitem">
                            @foreach ($section->getStudents() as $student)
                                <div class="deskcontainer {{$student->position === 0 ? 'unsaved': 'white'}} {{empty($student->favoriteColor) ? '': 'green'}}"
                                     style="{{empty($student->favoriteColor) ? '': 'background-color:' . $student->favoriteColor}}"
                                     position="{{$student->position}}" userid="{{$student->id}}">
                                    <div class="deskclose"><img src="../../public/Images/close.png"></div>
                                    <div class="deskicon">
                                        <img src="/images/header-default.jpg"
                                             realheader="/userPhoto/{{$student->id}}"/>
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
    <input type="hidden" name="hideIsStudent" id="hideIsStudent" value="{{$isStudent?"True":"False"}}" />
    <input type="hidden" name="browser" id="browser" value="{{$browser}}"/>
    <script src="{{ asset('/public/js/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('/public/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('/public/js/moment.min.js') }}"></script>
    <script src="{{ asset('/public/js/classdetail.js') }}"></script>
    <script src="{{ asset('/public/js/assignments.js') }}"></script>


@endsection