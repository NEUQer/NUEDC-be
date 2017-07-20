<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午5:12
 */
Route::group(['middleware' => ['token']], function () {
    /**
     * 获取学校队伍
     */
    Route::get("/school/team/info", 'SchoolAdminController@getSchoolTeams');
    /**
     * 添加学校队伍
     */
    Route::post("/school/team/add", 'SchoolAdminController@addSchoolTeam');
    /**
     * 更新学校队伍信息
     */
    Route::put("/school/team/update/{id}", 'SchoolAdminController@updateSchoolTeam');
    /**
     * 删除学校队伍
     */
    Route::delete("/school/team/delete/{id}", 'SchoolAdminController@deleteSchoolTeam');
    /**
     * 审核学校队伍
     */
    Route::put("/school/team/check/{id}",'SchoolAdminController@checkSchoolTeam');
    /**
     * 批量审核学校队伍
     */
    Route::put("/school/team/mult-check",'SchoolAdminController@checkSchoolTeams');
    /**
     * 查看学校队伍获奖情况
     */
    Route::get("/school/team/awards", 'SchoolAdminController@getSchoolResults');
    /**
     * 导出队伍信息
     */
    Route::get('/school/admin/team/export','SchoolAdminController@exportSchoolTeams');
    /**
     * 导出队伍获奖情况
     */
    Route::get('/school/admin/result/export','SchoolAdminController@exportSchoolResults');
    /**
     * 获取竞赛列表
     */
    Route::get('/school/admin/contest','SchoolAdminController@getStartedContest');

    /*
     * 获取队伍导入模板Excel
     */


    /*
     * 批量导入队伍
     */
    Route::post('/school/admin/team/import/{contestId}','SchoolAdminController@importTeams');
});

/**
 * 校管理员登录
 */
Route::post('/school/admin/login','SchoolAdminController@login');
Route::get('/school/admin/team/getImportTemplate','SchoolAdminController@getTeamListTemplateFile');

