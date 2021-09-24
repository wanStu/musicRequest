<?php


namespace app\common\model;


use think\Model;

class LiveServerModel extends Model
{
    protected $table = "live_server";
    protected $pk = "live_id";
}