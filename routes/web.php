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
    $userName = $cookieServices->GetCookiesOfUsername();
    if ($userName) {
        return redirect('/o365loginhint');
    }
    return redirect('/login');
});

Route::get('/o365loginhint', 'O365AuthController@o365LoginHint');
Route::get('/differentaccount', 'O365AuthController@differentAccountLogin');
Route::get('/oauth.php', 'O365AuthController@oauth');
Route::get('/o365login', 'O365AuthController@o365Login');

Route::get('/userlogout', function () {
    Session::flush();
    session_destroy();
    Auth::logout();
    return Redirect::to('/');
});

//login, register related.
Auth::routes();

//all schools, teachers and students, classes, class details.
Route::group(['middleware' => ['web', 'auth', 'SchoolMiddleware']], function () {
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

//user photo.
Route::get('/userPhoto/{o365UserId}', 'SchoolsController@userPhoto');

//link
Route::group(['middleware' => ['web']], function () {
    Route::get('/link', 'LinkController@index');
    Route::any('/link/createlocalaccount', 'LinkController@createLocalAccount');
    Route::any('/link/loginlocal', 'LinkController@loginLocal');
});

Route::group(['namespace' => 'Admin'], function () {
    Route::get('/admin/consent', 'AdminController@consent');
    Route::post('/admin/adminconsent', 'AdminController@AdminConsent');
    Route::get('/admin/processcode', 'AdminController@ProcessCode');

});

Route::get('/o365loginrequired', 'LinkController@loginO365Required');

//Admin functions.
Route::group(['middleware' => ['web', 'auth', 'Admin.Login'], 'namespace' => 'Admin'], function () {
    Route::get('/admin', 'AdminController@index');
    Route::post('/admin/adminunconsent', 'AdminController@AdminUnconsent');
    Route::post('/admin/enableuseraccess', 'AdminController@EnableUserAccess');
    Route::get('/admin/linkedaccounts', 'AdminController@MangeLinkedAccounts');
    Route::get('/admin/unlinkaccounts/{userId}', 'AdminController@UnlinkAccount');
    Route::post('/admin/dounlink/{userId}', 'AdminController@DoUnlink');
});

Route::get('/auth/aboutme', 'Auth\AboutMeController@index');
Route::post('/auth/savefavoritecolor', 'Auth\AboutMeController@SaveFavoriteColor');