<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/schools');
    }
    return redirect('/login');
});

//login, register related.
Auth::routes();

//all schools, teachers and students, classes, class details.
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/schools', 'SchoolsController@index');
});


Route::group(['middleware' => ['web']], function () {
  
    Route::get('/userlogout', 'Auth\LogoutController@logout');
});

Route::group(['middleware' => ['web']], function () {
    Route::get('/oauth.php', 'O365AuthController@oauth');
    Route::get('/o365login', 'O365AuthController@o365Login');
});
