<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/14
 * Time: 下午2:38
 */

Route::group(['prefix' => 'sysadmin'],function (){
    Route::get('/contests','SysAdminController@getAllContests');
});