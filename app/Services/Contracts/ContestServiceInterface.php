<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午11:25
 */

namespace App\Services\Contracts;


interface ContestServiceInterface
{
    function getAllContest();

    function updateSignUpContest(int $userId,array $signInfo):array ;

    function getContestSignUpStatus(int $userId,int $contestId):array ;

    function abandonContest(int $userId,int $contestId):bool ;

    function getContestProblemList(int $contestId,int $operatorId):array ;

    function getAllPassContestList(int $userId):array ;

    function getProblemDetail(int $userId,array $key);

    function updateProblemSelect(int $userId,array $key);

    function getContestResult(int $userId,int $contestId);
}