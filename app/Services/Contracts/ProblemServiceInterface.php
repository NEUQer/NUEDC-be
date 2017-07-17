<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 11:28
 */
namespace App\Services\Contracts;

interface ProblemServiceInterface
{
    function getProblemByContestId(int $contestId);

    function addProblem(array $problemData):bool ;

    function updateProblem(array $problemData):bool ;

    function deleteProblem($problemId):bool ;
}