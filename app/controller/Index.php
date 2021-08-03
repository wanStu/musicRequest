<?php


namespace app\controller;


use app\server\GetDataInDbServer;
use app\server\PlugFlow;
use app\server\UpdateFileInfoToDbServer;
use think\facade\Log;
use think\View;

/**
 * 获取 歌曲/视频 文件列表
 * @package app\controller
 */
class Index
{
    /**
     * 主页
     * @return string
     */
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
    public function updateFileDataToDb($type = "",array $fileList = []): string
    {
        if("" != $type) {
            if((new UpdateFileInfoToDbServer)->updateDb($type, $fileList)) {
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
        $result = (new UpdateFileInfoToDbServer)->updateFileStatusInDb("music");
        return Json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     *
     * @return View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFileList(string $type)
    {
        $data = (new GetDataInDbServer) -> getFileListInDb($type);
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
    public function validateUserPermission(string $ruleName, int $uid): string
    {
        $result = (new GetDataInDbServer)->validateUserPermission($ruleName,$uid);
        if ($result) {
            return "用户ID为 <span style='color: #39c5bb'>".$uid."</span> 的用户 有 <span style='color: #39c5bb'>".$ruleName."</span> 权限 ";
        }else {
            return "用户ID为 <span style='color: red'>".$uid."</span> 的用户 没有 <span style='color: red'>".$ruleName."</span> 权限 ";
        }
    }

    /**
     * 发布任务，推流到直播间
     * @param string $data 将被推流的视频路径
     * @param $ruleName
     * @param $uid
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function releaseLiveTask(string $data,$ruleName,$uid): string
    {
        $result = (new GetDataInDbServer)->validateUserPermission($ruleName, $uid);
        if(!$result) {
            return "点播失败!<br />可能的原因：<br />1. 您的权限不允许您点播 {$ruleName} 类型的作品<br />2. 系统内部故障，请将此错误报告给网站管理者";
        }

        if(is_file($data)) {
            $release = (new PlugFlow)->liveStart($data);
            if($release) {
                return "点播完成，等待播放吧";
            } else {
                return "点播失败!原因：未知";
            }
        }else {
            $fileFullName = explode("/",$data);
            $fileName = explode(".",$fileFullName[count($fileFullName) - 1])[0];
            $data = str_replace('/','\\',$data);
            Log::error("文件 【{$data}】 不存在，请检查文件");
            return "您选择的文件 【{$fileName}】 异常，请联系网站管理员";
        }
    }

}