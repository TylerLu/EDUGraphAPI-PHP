<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers;

class SchoolsController extends Controller
{


    public function __construct()
    {

    }


    public function index()
    {
        return view('schools.schools');
    }


}
