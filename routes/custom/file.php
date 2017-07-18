<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/18
 * Time: 下午11:55
 */

Route::options('/file/public/upload',function (){return response('');});
Route::post('/file/public/upload','FileController@uploadPublic');

Route::options('/file/private/upload',function(){return response('');});
Route::post('/file/private/upload','FileController@uploadPrivate');

Route::get('/file/private/get','FileController@getPrivate');