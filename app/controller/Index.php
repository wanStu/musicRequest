<?php


namespace app\controller;
use app\model\MusicFileListModel;
use app\model\VideoFileListModel;
use app\server\GetDataInDbServer;
use app\server\UpdateFileDataToDbServer;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

/**
 * 获取 歌曲/视频 文件列表
 * @package app\controller
 */
class Index
{
    public function index(): string
    {
        return app()->getRootPath();
//        return "once try";
    }

    /**
     * 更新数据库中的[音乐/视频]数据
     * @param array $fileList
     * $musicFileList = [
     *      序号 => 文件名,
     *      ......
     *      子目录名 => [
     *          序号 => 文件名,
     *          ......
     *      ]
     * ]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateFileData($type = "",array $fileList = []) {
        if($type == "") {

        }else {
            $result = (new UpdateFileDataToDbServer)->updateDb($type,$fileList);
        }
        return Json_encode($result);
    }

    /**
     * 更新数据库中状态非 -1(禁用) 的文件状态 若能在本地找到则状态为 1(正常) 找不到状态为 0(找不到资源)
     * @return false|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateFileStatusInDb() {
        $result = (new \app\server\UpdateFileDataToDbServer)->updateFileStatusInDb("music");
        return Json_encode($result);
    }

    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @return MusicFileListModel[]|VideoFileListModel[]|array|string|\think\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getList(string $type) {
        return (new GetDataInDbServer) -> getListInDb($type);
    }

}