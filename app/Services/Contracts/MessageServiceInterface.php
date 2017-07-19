<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:22
 */

namespace App\Services\Contracts;


Interface MessageServiceInterface
{
    function addMessage(array $messageInfo);

    function updateMessage(array $messageInfo,int $messageId);

    function getAllMessage(int $type,int $page,int $size);

    function getMessageDetail(int $messageId,int $type);

    function deleteMessage(int $messageId);
}