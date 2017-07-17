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
        $this->problemRepo->getBy('contest_id',$contestId);
    }

    function addProblem(array $problemData): bool
    {
        $this->problemRepo->insert($problemData);

        return true;
    }

    function updateProblem(array $problemData): bool
    {
        // TODO: Implement updateProblem() method.
    }

    function deleteProblem($problemId): bool
    {
        // TODO: Implement deleteProblem() method.
    }

}