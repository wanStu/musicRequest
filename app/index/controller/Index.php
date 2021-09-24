<?php


namespace app\index\controller;


use app\common\Base;
use app\common\model\PlayListModel;
use app\common\server\GetDataInDbServer;
use app\common\server\PlugFlow;
use app\common\server\UpdateFileInfoToDbServer;
use app\common\server\ValidateUser;
use think\facade\Log;

/**
 * 获取 歌曲/视频 文件列表
 * @package app\controller
 */
class Index extends Base
{
    /**
     * 主页
     * @return string
     */
    public function index()
    {
        return returnAjax(200,app()->getRootPath(),true);
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
    public function updateFileDataToDb($type = "",$fileList = [])
    {
        if($type == "" && !empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }
        if($fileList == "" && !empty($this->requestData["fileList"])) {
            $fileList = $this->requestData["fileList"];
        }
        if(in_array($type,$this::FILE_TYPE)) {
            $result = json_decode((new UpdateFileInfoToDbServer($this->app))->updateFileListToDb($type, $fileList)->getContent(),true);
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
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateFileStatusInDb($type = "") {
        if($type == "" && !empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }
        $result = json_decode((new UpdateFileInfoToDbServer($this->app))->updateFileStatusInDb("{$type}")->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFileList(string $type = "")
    {
        if($type == "" && !empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }
        $result = json_decode((new GetDataInDbServer($this->app))->getFileListInDb($type)->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 验证用户是否具有某项规则的权限
     * @param string $ruleName 规则名
     * @param int $uid 用户ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validateUserPermission(string $ruleName = "", int $user_id = 0)
    {
        if($user_id == 0) {
            $user_id = $this->userId;
        }
        if($ruleName == "" && !empty($this->requestData["ruleName"])) {
            $ruleName = $this->requestData["ruleName"];
        }
        if($user_id == 0 && !empty($this->requestData["user_id"])) {
            $user_id = $this->requestData["user_id"];
        }
        $ruleName = str_replace(" ","",$ruleName);
        $user_id = str_replace(" ","",$user_id);
        $result = json_decode((new ValidateUser($this->app))->validateUserPermission($ruleName,$user_id)->getContent(),true);
        if ($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
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
    public function releaseLiveTask(string $data = "",string $ruleName = "",int $user_id = 0)
    {
        if($user_id == 0) {
            $user_id = $this->userId;
        }
        if($ruleName == "" && !empty($this->requestData["ruleName"])) {
            $ruleName = $this->requestData["ruleName"];
        }
        if($user_id == 0 && !empty($this->requestData["user_id"])) {
            $user_id = $this->requestData["user_id"];
        }
        if($data == "" && !empty($this->requestData["data"])) {
            $data = $this->requestData["data"];
        }
        $result = json_decode((new ValidateUser($this->app))->validateUserPermission($this->requestData["ruleName"], $this->userId)->getContent(),true);
        if(!$result["data"]) {
            return returnAjax(100,$result["msg"],false);
        }
        if(is_file($data)) {
            $release = new PlugFlow($this->app);
            $releaseTaskResult = json_decode($release->liveStart($data,$user_id)->getContent(),true);
            if($releaseTaskResult["data"]) {
                return returnAjax(200,"点播完成，等待播放吧",true);
            } else {
                return returnAjax(100,$releaseTaskResult["msg"],false);
            }
        }else {
            $fileFullName = explode("/",$data);
            $fileName = explode(".",$fileFullName[count($fileFullName) - 1])[0];
            $data = str_replace('/','\\',$data);
            Log::error("文件 【{$data}】 不存在，请检查文件");
            return returnAjax(100,"您选择的文件 【{$fileName}】 异常，请联系网站管理员",false);
        }
    }

    /**
     * 开启 当播放列表为空时自动添加一个视频
     */
    public function randomRelease() {
        $plugFlow = new PlugFlow($this->app);
        $result = json_decode($plugFlow->RandomRelease()->getContent(),true);
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
        $result = PlayListModel::where("is_delete",0)->field("uid,file_name,create_time")->select()->toArray();
        return returnAjax(200,"获取成功",$result);
    }

}