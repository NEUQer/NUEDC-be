<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/14
 * Time: 下午2:38
 */

Route::group(['prefix' => 'sysadmin'],function (){
    Route::post('/login','SysAdminController@login');

    Route::get('/contests','SysAdminController@getAllContests');
    Route::post('/contest/create','SysAdminController@createContest');
    Route::post('/contest/{id}/update','SysAdminController@updateContest');
    Route::post('/contest/{id}/delete','SysAdminController@deleteContest');

    Route::get('/schools','SysAdminController@getSchools');
    Route::post('/school/create','SysAdminController@createSchool');
    Route::post('/school/{id}/update','SysAdminController@updateSchool');
    Route::get('/school/{id}/delete','SysAdminController@deleteSchool');
    Route::post('/school/import','SysAdminController@importSchools');

    Route::get('/school-admins','SysAdminController@getSchoolAdmins');
    Route::post('/school-admin/generate','SysAdminController@generateSchoolAdmin');
    Route::post('/school-admin/create','SysAdminController@addSchoolAdmin');

    Route::post('/user/{id}/update','SysAdminController@updateUser');
    Route::get('/user/{id}/delete','SysAdminController@deleteUser');

    Route::get('/contest-records','SysAdminController@getRecords');
    Route::post('/contest-record/update','SysAdminController@updateRecord');
    Route::get('/contest-record/delete','SysAdminController@deleteRecord');
    Route::get('/contest-record/export','SysAdminController@exportRecord');
    Route::get('/contest-record/export/all','SysAdminController@exportAllRecords');

    Route::get('/getSchoolListTemplateFile','SysAdminController@getSchoolListTemplateFile');

    Route::post('/contest-record/import','SysAdminController@importRecord');

    Route::put('/results/update', 'SysAdminController@updateResults');

    Route::post('/contest/{id}/result/check','SysAdminController@checkContestResult');
});

