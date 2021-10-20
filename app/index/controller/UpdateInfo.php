<?php


namespace app\index\controller;

use app\common\controller\Base;
use app\common\service\UpdateFileInfoToDbServer;
use thans\jwt\facade\JWTAuth;
use think\Request;

class UpdateInfo extends Base
{
    protected function initialize() {
        bind("UpdateFileInfoToDbServer",UpdateFileInfoToDbServer::class);
        $this->userId = JWTAuth::auth()["user_id"]->getValue();
    }
    /**
     * 更新数据库中的[音乐/视频]数据
     * @param Request
     *  type 类型
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateFileListDataToDb()
    {
        if(!empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }else {
            return returnAjax(100,"类型错误",false);
        }
        if(in_array($type,$this::FILE_TYPE)) {
            $result = json_decode((new UpdateFileInfoToDbServer())->updateFileListToDb($type)->getContent(),true);
            if($result["data"]) {
                return returnAjax(200,"更新成功",true);
            }else {
                return returnAjax(100,"未知错误",false);
            }
        }else {
            return returnAjax(100,"类型错误",false);
        }
    }


    /**
     * 更新数据库中状态非 -1(禁用) 的文件状态 若能在本地找到则状态为 1(正常) 找不到状态为 0(找不到资源)
     * @param Request
     *  type 类型
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateFileStatusInDb() {
        if(empty($this->requestData["type"])) {
            return returnAjax(100,"类型错误",false);
        }
        $result = json_decode(app("UpdateFileInfoToDbServer")->updateFileStatusInDb($this->requestData["type"])->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }
}