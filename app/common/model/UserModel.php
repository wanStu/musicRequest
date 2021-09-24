<?php


namespace app\common\model;


use think\Model;

class UserModel extends Model
{
    protected $table = "user";
    protected $pk = "user_id";
}