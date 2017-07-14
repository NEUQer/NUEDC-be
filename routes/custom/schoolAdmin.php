<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午5:12
 */

Route::get("/school/team/info", 'SchoolAdminController@getSchoolTeams');
Route::post("/school/team/add", 'SchoolAdminController@addSchoolTeam');
Route::put("/school/team/update/{id}", 'SchoolAdminController@updateSchoolTeam');
Route::delete("/school/team/delete/{id}", 'SchoolAdminController@deleteSchoolTeam');

Route::get("/school/team/awards", 'SchoolAdminController@getSchoolResults');