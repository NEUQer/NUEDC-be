<?php

namespace App\Http\Controllers;

use App\Common\ValidationHelper;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Exceptions\SchoolAdmin\SchoolNotExistedException;
use App\Services\ExcelService;
use Permission;
use App\Services\SchoolAdminService;
use Illuminate\Http\Request;

class SchoolAdminController extends Controller
{
    private $schoolAdminService;
    private $excelService;

    public function __construct(SchoolAdminService $schoolAdminService, ExcelService $excelService)
    {
        $this->schoolAdminService = $schoolAdminService;
        $this->excelService = $excelService;
    }

    /**
     * 校管理员登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'identifier' => 'required|string',
            'password' => 'required|min:6|max:20',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $loginName = $request->get("identifier");
        $password = $request->get("password");
        $ip = $request->ip();
        $client = 1;

        $data = $this->schoolAdminService->login($loginName, $password, $ip, $client);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 创建学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function addSchoolTeam(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'team_name' => 'required|string|max:255',
            'school_id' => 'required|integer',
            'school_name' => 'required|string|max:255',
            'contest_id' => 'required|integer',
            'school_level' => 'required|string|max:45',
            'member1' => 'required|string|max:255',
            'member2' => 'required|string|max:255',
            'member3' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:45',
            'email' => 'required|email'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $schoolTeamInfo = ValidationHelper::getInputData($request, $rules);

        if (!$this->schoolAdminService->addSchoolTeam($schoolTeamInfo)) {
            throw new UnknownException("fail to add school team");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 更新学校队伍信息
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function updateSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }
        $rules = [
            'team_name' => 'required|string|max:255',
            'school_id' => 'required|integer',
            'school_name' => 'required|string|max:255',
            'contest_id' => 'required|integer',
            'school_level' => 'required|string|max:45',
            'member1' => 'required|string|max:255',
            'member2' => 'required|string|max:255',
            'member3' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:45',
            'email' => 'required|email'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $schoolTeamData = ValidationHelper::getInputData($request, $rules);

        if (!$this->schoolAdminService->updateSchoolTeam($id, $schoolTeamData)) {
            throw new UnknownException("fail to update school team");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 删除学校队伍
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function deleteSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->schoolAdminService->deleteSchoolTeam($id)) {
            throw new UnknownException("fail to delete school team.");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 获取本校队伍信息（比赛前）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function getSchoolTeams(Request $request)
    {
        $conditions = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'integer',
            'status' => 'string|max:255'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $conditions['school_id'] = $request->user->school_id;
        $conditions['contest_id'] = $request->input('contest_id', -1);

        $page = $request->input('page', 1);
        $size = $request->input('size', -1);

        $data = $this->schoolAdminService->getSchoolTeams($conditions, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 查看本校队伍获奖情况
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function getSchoolResults(Request $request)
    {
        $conditions = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'required|integer',
            'result_info' => 'string|max:255'
        ]);

        if (!Permission::checkPermission($request->user->id, ['view_school_results'])) {
            throw new PermissionDeniedException();
        }

        $conditions['school_id'] = $request->user->school_id;

        $page = $request->input('page', 1);
        $size = $request->input('size', -1);

        $data = $this->schoolAdminService->getSchoolResults($conditions, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 审核本校队伍
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function checkSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'record_check' => 'required|string'
        ];

        $check = ValidationHelper::checkAndGet($request, $rules);

        if (!$this->schoolAdminService->checkSchoolTeam($id, $check['record_check'])) {
            throw new UnknownException("check fail");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 批量审核学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function checkSchoolTeams(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $schoolChecks = ValidationHelper::checkAndGet($request, [
            'checks' => 'required|array'
        ])['checks'];

        $rules = [
            'record_id' => 'required|integer|min:1',
            'record_check' => 'required|string',
        ];

        $fail = [];
        $success = [];

        foreach ($schoolChecks as $check) {
            ValidationHelper::validateCheck($check, $rules);
            if (!$this->schoolAdminService->checkSchoolTeam($check['record_id'], $check['record_check'])) {
                $fail[] = $check['record_id'];
            } else {
                $success[] = $check['record_id'];
            }
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'success' => $success,
                'fail' => $fail
            ]
        ]);
    }

    /**
     * 导出学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function exportSchoolTeams(Request $request)
    {
        $conditions = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'required|integer',
            'status' => 'string|max:255'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $conditions['school_id'] = $request->user->school_id;

        $data = $this->schoolAdminService->getSchoolTeams($conditions, 1, -1)['teams']->toArray();

        foreach ($data as &$datum) {
            unset($datum['contest_id']);
            unset($datum['school_id']);
            unset($datum['school_level']);
            unset($datum['school_name']);
            unset($datum['title']);
        }

        $rows = [];
        $rows[] = ['队伍编号', '队伍名称', '成员1姓名', '成员2姓名', '成员3姓名', '指导教师', '联系电话', '邮件', '队伍审核状态'];

        foreach ($data as $item) {
            $rows[] = array_values($item);
        }

        $this->excelService->export('报名情况', $rows);
    }

    /**
     * 导出学校获奖记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function exportSchoolResults(Request $request)
    {
        $conditions = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'required|integer',
            'result_info' => 'string|max:255'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $conditions['school_id'] = $request->user->school_id;

        $data = $this->schoolAdminService->getSchoolResults($conditions, 1, -1)['results']->toArray();

        foreach ($data as &$datum) {
            unset($datum['contest_id']);
            unset($datum['school_id']);
            unset($datum['school_name']);
            unset($datum['school_level']);
            unset($datum['problem_selected_at']);
        }

        $rows = [];
        $rows[] = ['队伍编号', '队伍名称', '成员1姓名', '成员2姓名', '成员3姓名', '指导教师', '联系电话', '邮件', '所选题目', '所得奖项', '现场赛相关信息'];

        foreach ($data as &$item) {
            $item = array_values($item);
            $item[8] = $item[13];
            unset($item[10]);
            unset($item[11]);
            unset($item[13]);
//            dd($item);
            $rows[] = $item;
        }

        $this->excelService->export('获奖情况', $rows);
    }

    /**
     * 获取已开始的比赛列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function getStartedContest(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $data = $this->schoolAdminService->getStartedContest();

        return response()->json([
            'code' => 0,
            'data' => [
                'contests' => $data
            ]
        ]);
    }

    public function getTeamListTemplateFile(ExcelService $excelService)
    {
        $name = "参赛队伍信息导入模板";

        $rows = [['队伍名称', '队长', '成员2', '成员3', '指导老师', '队长手机号', '邮箱']];

        $excelService->export($name, $rows);
    }

    public function importTeams(Request $request, int $contestId)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $data = $this->excelService->import($file);
        }

        $contestTeams = $data['rows'];

        //去掉表头
        array_pull($contestTeams, 0);
        //用于保存成功与失败记录
        $success = [];
        $fail = [];

        $schoolInfo = $this->schoolAdminService->getSchoolDetail($request->user->school_id);

        if ($schoolInfo == null)
            throw new SchoolNotExistedException();

        foreach ($contestTeams as $contestTeam) {
            $contestTeam[5] = (string)$contestTeam[5];

            //根据模板填充信息
            $condition = [
                'contest_id' => $contestId,
                'team_name' => $contestTeam[0],
                'member1' => $contestTeam[1],
                'member2' => $contestTeam[2],
                'member3' => $contestTeam[3],
                'teacher' => $contestTeam[4],
                'contact_mobile' => $contestTeam[5],
                'email' => $contestTeam[6],
                'school_name' => $request->user->school_name,
                'school_id' => $request->user->school_id,
                'school_level' => $schoolInfo['level']
            ];

            if ($this->schoolAdminService->addSchoolTeam($condition)) {
                $success[] = $contestTeam;
            } else {
                $fail[] = $contestTeam;
            }
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'success' => $success,
                'fail' => $fail
            ]
        ]);
    }
    public function updateProblemSelect(Request $request){

        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'id'=>'required|integer',
            'problemId'=>'required|integer'
        ];

        $info = ValidationHelper::checkAndGet($request,$rules);

        $this->schoolAdminService->updateTeamProblem($request->user->school_id,$info['id'],$info['problemId']);

        return response()->json(
            [
                'code'=>0
            ]
        );
    }

    public function checkTeamProblem(Request $request){

        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'contestId'=>'required|integer',
            'status'=>'required|string'
        ];

        $info = ValidationHelper::checkAndGet($request,$rules);

        $this->schoolAdminService->checkTeamProblem($info['contestId'],$request->user->school_id,$info['status']);

        return response()->json(
            [
                'code'=>0
            ]
        );
    }
}
