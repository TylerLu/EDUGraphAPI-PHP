<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
?>

@extends('layouts.app')
@section('title', 'ALL SCHOOLS')
@section('content')
    <div class="row schools">
        <div class="tophero">
            <div class="col-md-6">
                <div class="a-heading schoolname">ALL SCHOOLS</div>
            </div>

            <div class="container myschool">
                <div class="schoolenrolled">Current school(s) enrolled</div>
                <div class="greenicon"></div>
            </div>
            <div style="clear:both;"></div>
            <table class="table  table-green table-schools">

                    <tbody>
                    <tr class="table-green-header">
                        <th class="tdleft">School Name</th>
                        <th>Principal</th>
                        <th>Grade Levels</th>
                        <th>Address</th>
                        <th></th>
                    </tr>
                   
                    @if(empty($schools) || count($schools)==0 )
                        <tr>
                            <td colspan="5">
                                <div class="nodata"> There is no data available for this page at this time.</div>
                            </td>
                        </tr>
                    @endif
                    @foreach($schools as $school)
                        <tr class="tr-content {{$school->isMySchool ? 'td-green' : ''}}">
                            <td>{{$school->displayName }}</td>
                            <td>
                                @if($school->principalName)
                                    {{$school->principalName }}
                                 @else
                                    -
                                @endif
                            </td>
                            <td>{{$school->lowestGrade }} - {{$school->highestGrade }}</td>
                            <td>
                                <div class="schooladdress">
                                    {{$school->address}}
                                    @if($school->city)
                                        <br/>
                                    @endif
                                    {!! $school->getCompoundAddress() !!}
                                    @if(!$school->address && !$school->city)
                                        -
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a class="btnlink" target="_self" href="/classes/{{$school->id}}">Classes</a>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>

            </table>
        </div>

    </div>
@endsection
