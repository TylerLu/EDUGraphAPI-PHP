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
use App\Services\CookieService;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/schools');
    }
    $cookieServices = new CookieService();
    $userName = $cookieServices->getCookiesOfUsername();
    if ($userName) {
        return redirect('/o365loginhint');
    }
    return redirect('/login');
});

//login, register related.
Auth::routes();

//all schools, teachers and students, classes, class details.
Route::group(['middleware' => ['web', 'auth', 'LinkRequired']], function () {
    Route::get('/schools', 'SchoolsController@index');
    Route::get('/users/{objectId}', 'SchoolsController@users');
    Route::get('/users/next/{objectId}/{skipToken}', 'SchoolsController@usersNext');
    Route::get('/students/next/{objectId}/{skipToken}', 'SchoolsController@studentsNext');
    Route::get('/teachers/next/{objectId}/{skipToken}', 'SchoolsController@teachersNext');
    Route::get('/classes/{objectId}', 'SchoolsController@classes');
    Route::get('/class/{objectId}/{classId}', 'SchoolsController@classDetail');
    Route::get('/classes/next/{schoolId}/{skipToken}', 'SchoolsController@classesNext');
    Route::post('/saveSeatingArrangements', 'SchoolsController@saveSeatingArrangements');
});


Route::group(['middleware' => ['web']], function () {
    Route::get('/link', 'LinkController@index');
    Route::any('/link/createlocalaccount', 'LinkController@createLocalAccount');
    Route::any('/link/loginlocal', 'LinkController@loginLocal');

    Route::get('/userPhoto/{o365UserId}', 'UserPhotoController@userPhoto');
    Route::get('/auth/aboutme', 'Auth\AboutMeController@index');

    Route::get('/o365loginrequired', 'LinkController@loginO365Required');
    Route::post('/auth/savefavoritecolor', 'Auth\AboutMeController@saveFavoriteColor');
    Route::get('/o365loginhint', 'O365AuthController@o365LoginHint');
    Route::get('/differentaccount', 'O365AuthController@differentAccountLogin');
    Route::get('/oauth.php', 'O365AuthController@oauth');
    Route::get('/o365login', 'O365AuthController@o365Login');
    Route::get('/userlogout', 'Auth\LogoutController@logout');

});

Route::group(['namespace' => 'Admin'], function () {
    Route::get('/admin/consent', 'AdminController@consent');
    Route::post('/admin/adminconsent', 'AdminController@adminConsent');
    Route::get('/admin/processcode', 'AdminController@processCode');

});

//Admin functions.
Route::group(['middleware' => ['web', 'auth', 'AdminOnly'], 'namespace' => 'Admin'], function () {
    Route::get('/admin', 'AdminController@index');
    Route::post('/admin/adminunconsent', 'AdminController@adminUnconsent');
    Route::post('/admin/enableuseraccess', 'AdminController@enableUserAccess');
    Route::get('/admin/linkedaccounts', 'AdminController@manageLinkedAccounts');
    Route::get('/admin/unlinkaccounts/{userId}', 'AdminController@unlinkAccount');
    Route::post('/admin/dounlink/{userId}', 'AdminController@doUnlink');
});


