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
    function getProblemByContestId(int $contestId, int $page, int $size);

    function addProblem(array $problemData): int;

    function updateProblem(array $condition, array $problemData): bool;

    function deleteProblem(array $condition): bool;
}