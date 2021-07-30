<?php


namespace app\server;


use app\model\MusicFileListModel;
use app\model\VideoFileListModel;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * 从数据库中获取数据
 * Class getDataInDbServer
 * @package app\server
 */
class GetDataInDbServer
{
    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @return array|MusicFileListModel[]|string|Collection|VideoFileListModel[] 以Json格式返回数据库中的音乐/视频列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getListInDb(string $type) {
        if($type == "music") {
            $db = new MusicFileListModel();
        }else if($type == "video") {
            $db = new VideoFileListModel();
        }else {
            return "类型错误";
        }
        return $db->where($type."_status",1)->select();
    }
}