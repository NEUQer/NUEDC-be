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

    public function getProblemByContestId(int $contestId, int $page, int $size)
    {
        $count = $this->problemRepo->getWhereCount(['contest_id' => $contestId]);

        $problems = $this->problemRepo->paginate($page, $size, ['contest_id' => $contestId]);

        return [
            'count' => $count,
            'problems' => $problems
        ];
    }

    public function addProblem(array $problemData): int
    {
        return $this->problemRepo->insertWithId($problemData);
    }

    public function updateProblem(array $condition, array $problemData): bool
    {
        return $this->problemRepo->updateWhere($condition, $problemData) == 1;
    }

    public function deleteProblem(array $condition): bool
    {
        return $this->problemRepo->deleteWhere($condition) == 1;
    }

}