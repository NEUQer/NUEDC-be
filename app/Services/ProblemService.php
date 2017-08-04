<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 13:13
 */

namespace App\Services;

use App\Exceptions\Contest\ProblemSelectTimeException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Exceptions\Problem\ProblemNotExistException;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\ProblemRepository;
use App\Services\Contracts\ProblemServiceInterface;
use Carbon\Carbon;

class ProblemService implements ProblemServiceInterface
{
    private $problemRepo;
    private $contestRecordRepo;
    private $contestRepo;

    public function __construct(ProblemRepository $problemRepository, ContestRecordRepository $contestRecordRepository, ContestRepository $contestRepository)
    {
        $this->problemRepo = $problemRepository;
        $this->contestRecordRepo = $contestRecordRepository;
        $this->contestRepo = $contestRepository;
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

    public function canUserAccessProblem(int $userId, int $problemId): bool
    {
        $problem = $this->problemRepo->get($problemId, ['id', 'contest_id']);

        if ($problem === null) {
            throw new ProblemNotExistException();
        }

        $count = $this->contestRecordRepo->getWhereCount(['contest_id' => $problem->contest_id, 'register_id' => $userId, 'status' => '已通过']);

        if ($count === 0) {
            throw new PermissionDeniedException();
        }

        // 检查竞赛的时间

        $contest = $this->contestRepo->get($problem->contest_id, ['id', 'problem_start_time', 'problem_end_time', 'can_select_problem']);

        if ($contest->can_select_probelm === 1) {
            return true;
        }

        if ($contest->can_select_probelm === -1) {
            $currentTime = strtotime(Carbon::now());
            if (strtotime($contest->problem_start_time) > $currentTime||$currentTime > strtotime($contest->problem_end_time)) {
                return false;
            }
        }

        if ($contest->can_select_problem === 0) {
            return false;
        }

        return true;
    }

    public function getProblem(int $id, array $columns = ['*'])
    {
        return $this->problemRepo->get($id,$columns);
    }

}