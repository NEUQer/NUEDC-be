<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:32
 */

namespace App\Repository\Eloquent;


use App\Repository\Traits\InsertWithIdTrait;
use Illuminate\Support\Facades\DB;

class MessageRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\Message";
    }

    use InsertWithIdTrait;

    public function getPre(int $id,int $type){
       return DB::select('select id,title from messages where id = (select id from messages where id <  '.$id.' and type ='.$type.' order by id desc limit 1)');
    }

    public function getNext(int $id,int $type){
        return DB::select('select id,title from messages where id = (select id from messages where id >  '.$id.' and type ='.$type.' order by id asc limit 1)');
    }
}