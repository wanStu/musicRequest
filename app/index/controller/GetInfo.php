<?php


namespace app\index\controller;


use app\common\controller\Base;
use app\common\service\GetDataInMinIO;
use thans\jwt\facade\JWTAuth;
use app\common\service\User as UserService;
class GetInfo extends Base
{
    protected function initialize() {
        bind("UserService",UserService::class);
        $this->userId = JWTAuth::auth()["user_id"]->getValue();
    }
    /**
     * 获取MinIO中的音乐/视频列表
     * @param Request
     *  type 类型
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getObjectList()
    {
        if(!empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }else {
            return returnAjax(100,"类型不能为空",false);
        }
        $result = json_decode((new GetDataInMinIO())->getObjectList($type)->getContent(),true);
        return returnAjax(200,"获取成功",$result);
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo() {
        if(empty($this->requestData["user_id"])) {
            $user_id = $this->userId;
        }else {
            $user_id = $this->requestData["user_id"];
        }
        $result = json_decode(app("UserService")->getUserInfo($user_id)->getContent(),true);
        return returnAjax(200,$result["msg"],$result["data"]);
    }
    /**
     * 获取分数详情
     */
    public function getUserScoreInfo() {
        if(empty($this->requestData["user_id"])) {
            $user_id = $this->userId;
        }else {
            $user_id = $this->requestData["user_id"];
        }
        $result = json_decode(app("UserService")->getUserScoreInfo($user_id)->getContent(),true);
        return returnAjax(200,$result["msg"],$result["data"]);
    }
}