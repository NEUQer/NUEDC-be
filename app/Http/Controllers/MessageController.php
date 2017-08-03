<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:07
 */

namespace App\Http\Controllers;


use App\Common\ValidationHelper;
use App\Services\MessageService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class MessageController extends Controller
{


    private $messageService;

    private $permissionService;



    public function __construct(MessageService $messageService,PermissionService $permissionService)
    {
        $this->messageService = $messageService;
        $this->permissionService = $permissionService;
    }

    public function addMessage(Request $request){
        $rules = [
            'type'=>'required|integer|min:0|max:1',
            'title'=>'required|max:200',
            'content'=>'required'
        ];

        $messageInfo = ValidationHelper::checkAndGet($request,$rules);

        $permission =  ($messageInfo['type'] >0) ? 'manage_notice' : 'manage_news';

        $this->permissionService->checkPermission($request->user->id,[$permission]);

        return response()->json([
            'code'=>0,
            'data'=>[
                'messageId'=>$this->messageService->addMessage($messageInfo)
            ]
        ]);
    }

    public function getAllMessage(Request $request){
        ValidationHelper::validateCheck($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:100',
            'type' => 'required'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);
        $type = $request->input('type');

        return response()->json(
            [
                'code'=>0,
                'data'=>$this->messageService->getAllMessage($type,$page,$size)
            ]
        );
    }

    public function getMessageDetail(Request $request){


        ValidationHelper::validateCheck($request->all(),['messageId'=>'required','type'=>'required']);

        $messageId = $request->input('messageId');

        $type = $request->input('type');
        return response()->json(
            [
                'code'=>0,
                'data'=>$this->messageService->getMessageDetail($messageId,$type)
            ]
        );

    }

    public function updateMessage(Request $request,int $messageId){
        $rules = [
            'title'=>'required',
            'content'=>'required',
            'type'=>'required'
        ];

        $messageInfo = ValidationHelper::checkAndGet($request,$rules);

        $permission =  ($messageInfo['type'] >0) ? 'manage_notice' : 'manage_news';

        $this->permissionService->checkPermission($request->user->id,[$permission]);

        $this->messageService->updateMessage($messageInfo,$messageId);

        return response()->json(
            [
                'code'=>0
            ]
        );
    }

    public function deleteMessage(Request $request,int $messageId){

        $messageInfo = $this->messageService->getMessage($messageId);

        $permission =  ($messageInfo['type'] >0) ? 'manage_notice' : 'manage_news';

        $this->permissionService->checkPermission($request->user->id,[$permission]);


        $this->messageService->deleteMessage($messageId);

        return response()->json(
            [
                'code'=>0
            ]
        );
    }
}