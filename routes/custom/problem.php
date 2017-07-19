<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/18
 * Time: 15:40
 */

Route::group(['middleware' => 'token', 'prefix' => '/sysadmin'], function () {
    Route::group(['prefix' => '/problem'], function () {
        Route::post('/add', 'ProblemController@addProblem');
        Route::get('/info', 'ProblemController@getProblems');
        Route::get('/info/{id}','ProblemController@getProblemInfo');
        Route::put('/update/{id}', 'ProblemController@updateProblem');
        Route::delete('/delete/{id}', 'ProblemController@deleteProblem');
    });
});