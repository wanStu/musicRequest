<?php


namespace app\controller;


use app\server\GetDataInDbServer;
use app\server\UpdateFileDataToDbServer;
use think\View;

/**
 * 获取 歌曲/视频 文件列表
 * @package app\controller
 */
class Index
{
    public function index(): string
    {
        return app()->getRootPath();
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateFileData($type = "",array $fileList = []) {
        if($type != "") {
            if((new UpdateFileDataToDbServer)->updateDb($type, $fileList)) {
                return "成功";
            }else {
                return "未知错误";
            }
        }else {
            return "类型错误";
        }
    }

    /**
     * 更新数据库中状态非 -1(禁用) 的文件状态 若能在本地找到则状态为 1(正常) 找不到状态为 0(找不到资源)
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateFileStatusInDb() {
        $result = (new UpdateFileDataToDbServer)->updateFileStatusInDb("music");
        return Json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @return View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(string $type)
    {
        $data = (new GetDataInDbServer) -> getListInDb($type);
        return view("",["data" => $data]);
    }

    /**
     * 验证用户是否具有某项规则的权限
     * @param string $ruleName 规则名
     * @param int $uid 用户ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validatePermission(string $ruleName, int $uid) {
        $result = (new GetDataInDbServer)->validateUserPermission($ruleName,$uid);
        echo $result;
    }

}