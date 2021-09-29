<?php


namespace app\index\controller;

class Index
{
    /**
     * 主页
     * @return string
     */
    public function index()
    {
        (new \app\common\model\UserScoreModel)->where("user_id",55)->find()->save(["dsasadsa" => 1,"user_name" => "222","user_pwd" => 1,"sadsaasd" => 1]);
        return returnAjax(200,app()->getRootPath(),true);
    }
}