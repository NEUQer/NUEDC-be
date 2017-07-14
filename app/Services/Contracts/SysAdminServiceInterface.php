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

    function deleteContest(array $condition):bool;

    // 题目管理

//    function getProblems();
//
//    function createProblem(array $problem):int;
//
//    function updateProblem()

    // 学校管理员

    function generateSchoolAdmin(int $schoolId,array $schoolAdmin);

    function updateSchoolAdmin(int $userId,array $data):bool;

    function deleteSchoolAdmin(int $userId):bool;
}