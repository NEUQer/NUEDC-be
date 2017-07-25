<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/6/30
 * Time: 下午11:43
 */


Route::group(['prefix' => '/user'], function() {
    Route::post('/register','UserController@register');
    Route::post('/login','UserController@login');
    Route::get('/preRegister','UserController@perRegister');
    Route::get('/schools','UserController@getSchools');
    Route::get('/problem/{id}/attachment','UserController@getProblemAttach');
    Route::get('/verifyCode','UserController@getVerifyCode');
});

Route::group(['middleware'=>'token'],function (){
    Route::group(['prefix' => '/user'],function (){
       Route::post('/updatePassword','UserController@updateUserPassword');
       Route::get('/logout','UserController@logout');
    });

});

Route::group(['middleware' => 'user'], function() {

    Route::group(['prefix' => '/user'], function() {
        Route::post('/signUpContest','UserController@signUpContest');
        Route::get('/getAllContest','UserController@getAllContest');
        Route::get('/{contestId}/getContestProblemList','UserController@getContestProblemList');
        Route::get('/getContestProblemDetail','UserController@getContestProblemDetail');
        Route::get('/updateContestProblemSelect','UserController@updateContestProblemSelect');
        Route::get('/{contestId}/getResult','UserController@getContestResultStatus');
        Route::get('/{contestId}/getContestSignUpStatus','UserController@getContestSignUpStatus');
        Route::get('/{contestId}/abandonContest','UserController@abandonContest');
        Route::get('/getAllPassContest','UserController@getAllPassContest');
        Route::get('/getSignedUpContest','UserController@getSignedUpContest');
        Route::post('/password/forget','UserController@forgetPassword');

    });

});