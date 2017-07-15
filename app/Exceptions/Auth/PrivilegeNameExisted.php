<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午1:12
 */

namespace App\Exceptions\Auth;


use App\Exceptions\BaseException;

class PrivilegeNameExisted extends BaseException
{
    protected $code = 20007;

    protected $data = 'privilege name have existed,can not add';

    public function __construct(string $column)
    {
        parent::__construct();
        $this->data = [
            'message' => 'a privilege with same '.$column.' is existed',
            'column' => $column
        ];
    }
}