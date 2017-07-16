<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午5:12
 */
Route::group(['middleware' => ['token']], function () {
    Route::get("/school/team/info", 'SchoolAdminController@getSchoolTeams');
    Route::post("/school/team/add", 'SchoolAdminController@addSchoolTeam');
    Route::put("/school/team/update/{id}", 'SchoolAdminController@updateSchoolTeam');
    Route::delete("/school/team/delete/{id}", 'SchoolAdminController@deleteSchoolTeam');
    Route::put("/school/team/check/{id}",'SchoolAdminController@checkSchoolTeam');
    Route::get("/school/team/awards", 'SchoolAdminController@getSchoolResults');
    Route::get('/school/admin/team/export','SchoolAdminController@exportSchoolTeams');
    Route::get('/school/admin/result/export','SchoolAdminController@exportSchoolResults');
});
Route::post('/school/admin/login','SchoolAdminController@login');


