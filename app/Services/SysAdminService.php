<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/13
 * Time: 上午11:23
 */

namespace App\Services;


use App\Repository\Eloquent\ContestRepository;
use App\Services\Contracts\SysAdminServiceInterface;

class SysAdminService implements SysAdminServiceInterface
{
    private $contestRepo;

    public function __construct(ContestRepository $contestRepository)
    {
        $this->contestRepo = $contestRepository;
    }

    public function getContests()
    {
        return $this->contestRepo->all([
            'id','title','status','can_register',
            'can_select_problem','can_select_problem','register_start_time',
            'register_end_time','problem_start_time','problem_end_time'
        ]);
    }

    public function createContest(array $contest): int
    {
        return $this->contestRepo->insertWithId($contest);
    }

    public function updateContest(array $condition, array $contest): bool
    {
        return $this->contestRepo->updateWhere($condition,$contest) == 1;
    }

    public function deleteContest(array $condition): bool
    {
        return $this->contestRepo->deleteWhere($condition) == 1;
    }
}