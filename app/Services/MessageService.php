<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:21
 */

namespace App\Services;


use App\Exceptions\Message\MessageNotExistedException;
use App\Repository\Eloquent\MessageRepository;
use App\Services\Contracts\MessageServiceInterface;

class MessageService implements MessageServiceInterface
{

    private $messageRepo;

    /**
     * MessageService constructor.
     * @param $messageRepo
     */
    public function __construct(MessageRepository $messageRepo)
    {
        $this->messageRepo = $messageRepo;
    }

    function addMessage(array $messageInfo)
    {
       return $this->messageRepo->insertWithId($messageInfo);
    }

    function deleteMessage(int $messageId)
    {
        return $this->messageRepo->deleteWhere(['id'=>$messageId]) == 1;
    }


    function getAllMessage(int $type,int $page,int $size)
    {
        $messages = $this->messageRepo->paginate($page, $size, ['type'=>$type], [
            'id', 'type', 'title', 'created_at', 'updated_at']);

        $count = $this->messageRepo->getWhereCount(['type'=>$type]);

        return [
            'messages' => $messages,
            'count' => $count
        ];
    }


    function getMessageDetail(int $messageId,int $type)
    {
        $message = $this -> messageRepo->get($messageId);
        if ( $message == null)
            throw new MessageNotExistedException();
        $pre = $this->messageRepo->getPre($messageId,$type);

        $next = $this->messageRepo->getNext($messageId,$type);

        return [
            'current'=>$message,
            'pre'=>$pre,
            'next'=>$next
        ];
    }


    function updateMessage(array $messageInfo,int $messageId)
    {
        return $this->messageRepo->update($messageInfo,$messageId);
    }

    function getMessage(int $messageId)
    {
        return $this->messageRepo->get($messageId);
    }
}