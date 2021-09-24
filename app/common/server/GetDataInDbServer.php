<?php


namespace app\common\server;


use app\common\Base;
use app\common\model\MusicFileListModel;
use app\common\model\UserModel;
use app\common\model\VideoFileListModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 从数据库中获取数据
 * Class getDataInDbServer
 * @package app\server
 */
class GetDataInDbServer extends Base
{
    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @return array|string 以Array格式返回数据库中的音乐/视频列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getFileListInDb(string $type = "") {
        if($type == "" && !empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }
        if("music" == $type) {
            $db = new MusicFileListModel();
        }else if($type == "video") {
            $db = new VideoFileListModel();
        }else {
            return returnAjax(100, "类型错误",false);
        }
        $result = $db->where($type."_status",1)->select()->toArray();
        if($result) {
            return returnAjax(200,$result,true);
        }else {
            if(0 == count($result)) {
                return returnAjax(200,"暂无数据",true);
            }else {
                return returnAjax(100,"意外的错误",false);
            }
        }

    }

}