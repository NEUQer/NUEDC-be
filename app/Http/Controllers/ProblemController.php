<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 16:46
 */

namespace App\Http\Controllers;

use App\Common\ValidationHelper;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Facades\Permission;
use App\Services\ProblemService;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    private $problemService;

    public function __construct(ProblemService $problemService)
    {
        $this->problemService = $problemService;
    }

    public function addProblem(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_problems'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'contest_id' => 'required|integer|max:11',
            'title' => 'required|string',
            'content' => 'required|string',
            'attach_path' => 'string,max:255',
            'add_on' => 'string'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $problemData = ValidationHelper::getInputData($request, $rules);

        return response()->json([
            'code' => 0,
            'data' => [
                'problem_id' => $this->problemService->addProblem($problemData)
            ]
        ]);
    }

    public function updateProblem(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_problems'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'attach_path' => 'string|max:255',
            'add_on' => 'string'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $problemData = ValidationHelper::getInputData($request, $rules);

        if (!$this->problemService->updateProblem(['id' => $id], $problemData)) {
            throw new UnknownException("fail to update problem.");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteProblem(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_problems'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->problemService->deleteProblem(['id' => $id])) {
            throw new UnknownException("fail to delete problem");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function getProblems(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_problems'])) {
            throw new PermissionDeniedException();
        }

        ValidationHelper::validateCheck($request->all(), [
            'contest_id' => 'integer|min:1',
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:500'
        ]);

        $contestId = $request->get("contest_id");
        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        $data = $this->problemService->getProblemByContestId($contestId, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getProblemInfo(Request $request, $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_problems'])) {
            throw new PermissionDeniedException();
        }

        $data = $this->problemService->getProblem($id);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }
}