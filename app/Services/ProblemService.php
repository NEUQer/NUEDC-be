<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 13:13
 */

namespace App\Services;

use App\Repository\Eloquent\ProblemRepository;
use App\Services\Contracts\ProblemServiceInterface;

class ProblemService implements ProblemServiceInterface
{
    private $problemRepo;

    public function __construct(ProblemRepository $problemRepository)
    {
        $this->problemRepo = $problemRepository;
    }

    function getProblemByContestId(int $contestId)
    {
        $this->problemRepo->getBy('contest_id', $contestId)->all();
    }

    function addProblem(array $problemData): bool
    {
        return $this->problemRepo->insertWithId($problemData);
    }

    function updateProblem(array $condition, array $problemData): bool
    {
        return $this->problemRepo->updateWhere($condition, $problemData) == 1;
    }

    function deleteProblem(array $condition): bool
    {
        return $this->problemRepo->deleteWhere($condition) == 1;
    }

}