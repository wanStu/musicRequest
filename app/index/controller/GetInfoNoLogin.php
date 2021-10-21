<?php

namespace app\index\controller;

use app\common\model\PlaylistModel;
use app\common\service\GetDataInDbServer;
use app\common\service\UserGroup as UserGroupService;

class GetInfoNoLogin
{
    public function __construct() {
        bind("GetDataInDbServer",GetDataInDbServer::class);
        bind("UserGroupService",UserGroupService::class);
    }
    /**
     * 获取数据库中的音乐/视频列表
     * @param Request
     *  type 类型
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFileList()
    {
        if(!empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }else {
            return returnAjax(100,"类型错误",false);
        }
        $result = json_decode(app("GetDataInDbServer")->getFileListInDb($type)->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }
    /**
     * 获取播放列表
     */
    public function getPlaylist() {
        $result = PlaylistModel::where("is_delete",0)->field("user_id,file_name,create_time")->select()->toArray();
        return returnAjax(200,"获取成功",$result);
    }

    public function getUserGroupList() {
        $getUserGroupListResult = json_decode(app("UserGroupService")->getUserGroupList()->getContent(),true);
        if(false === $getUserGroupListResult["data"]) {
            return returnAjax(100,$getUserGroupListResult["msg"],false);
        }else {
            return returnAjax(200,$getUserGroupListResult["msg"],$getUserGroupListResult["data"]);
        }
    }
}