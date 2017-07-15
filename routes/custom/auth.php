<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午12:49
 */


Route::group(['middleware' => 'privilege'], function() {
    Route::group(['prefix' => '/auth'], function() {
        Route::post('/updateSysPrivilege','AuthController@updateSysPrivilege');
        Route::post('/updateUserPrivilege','AuthController@updateUserPrivileges');
        Route::get('/getAllPrivilegeInfo','AuthController@getAllPrivilegeInfo');
        Route::get('/getAllRoleInfo','AuthController@getAllRoleInfo');
        Route::get('/getUserPermissionInfo','AuthController@getUserPermissionInfo');
        Route::post('/giveRoleToCustom','AuthController@giveRoleToCustom');
        Route::post('/updateRoleInfo','AuthController@updateRoleInfo');
        Route::post('/deleteRole','AuthController@deleteRole');
        Route::post('/createRole','AuthController@createRole');
    });
});