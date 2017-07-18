<?php

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

include 'custom/user.php';
include 'custom/sysAdmin.php';
include 'custom/schoolAdmin.php';
include 'custom/auth.php';
include 'custom/problem.php';
include 'custom/file.php';

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'token'],function (){
    Route::get('/verify-token',function (\Illuminate\Http\Request $request) {
        return [
            'code' => 0,
            'user' => $request->user
        ];
    });
});

Route::get('/test','TestController@test');
Route::get('/test/excel/export','TestController@export');
Route::post('/test/excel/import','TestController@import');
