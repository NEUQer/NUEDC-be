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
}