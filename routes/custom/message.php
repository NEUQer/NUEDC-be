<?php

/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/19
 * Time: 上午11:15
 */

Route::group(['middleware' => 'token', 'prefix' => '/sysadmin'], function () {
    Route::group(['prefix' => '/message'], function () {
        Route::post('/add', 'MessageController@addMessage');
        Route::put('/update/{id}', 'MessageController@updateMessage');
        Route::delete('/delete/{id}', 'MessageController@deleteMessage');
    });
});

Route::get('/sysadmin/all', 'MessageController@getAllMessage');
Route::get('/sysadmin/info/{id}', 'MessageController@getMessageDetail');