<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/13
 * Time: 上午11:10
 */

namespace App\Services\Contracts;


interface SysAdminServiceInterface
{
    function login(string $loginName,string $password,string $ip);

    // 竞赛管理
    function getContests();

    function createContest(array $contest):int;

    function updateContest(array $condition,array $contest):bool;

    function deleteContest(int $id):bool;

    // todo 题目管理

//    function getProblems();
//
//    function createProblem(array $problem):int;
//
//    function updateProblem()

    // 学校管理员

    function getSchoolAdmins(int $page,int $size);

    function generateSchoolAdmin(array $schoolIds);

    function createSchoolAdmins(array $user);

    function updateUser(int $userId,array $data):bool;

    function deleteUser(int $userId):bool;

    // todo 学校管理

    function importSchools(array $schools);

    function createSchool(array $data):int;

    function getSchools(int $page,int $size);

    function updateSchool(int $schoolId,array $data):bool;

    function deleteSchool(int $schoolId):bool;

    // todo 参赛情况管理

    function getRecords(int $page,int $size,array $condition);

    function updateRecord(array $update):bool;

    function deleteRecord(array $recordIds):bool;

    // todo 成绩录入

    function updateResults(array $results):bool;

}