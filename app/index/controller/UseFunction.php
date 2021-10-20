<?php


namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\JobsModel;
use app\common\model\MusicFileListModel;
use app\common\model\PlaylistModel;
use app\common\model\VideoFileListModel;
use app\common\service\GetDataInMinIO;
use app\common\service\Permission;
use app\common\service\Playlist;
use app\common\service\UpdateDataToMinIO;
use app\common\service\ValidateUser;
use app\Request;
use thans\jwt\facade\JWTAuth;
use think\facade\Log;
use think\facade\Queue;
use Redis;

class UseFunction extends Base
{
    protected function initialize() {
        bind("Permission",Permission::class);
        bind("Playlist",Playlist::class);
        bind("ValidateUser",ValidateUser::class);
        bind("UpdateDataToMinIO",UpdateDataToMinIO::class);
        $this->userId = JWTAuth::auth()["user_id"]->getValue();
    }
    /**
     * 验证用户是否具有某项规则的权限
     * @param Request
     *  rule_name 规则名
     *  user_id 用户ID 当不传时使用当前登录用户id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validateUserPermission()
    {
        if(!empty($this->requestData["rule_name"])) {
            $ruleName = $this->requestData["rule_name"];
        }else {
            return returnAjax(100,"规则错误",false);
        }
        if(!empty($this->requestData["user_id"])) {
            $user_id = $this->requestData["user_id"];
        }else {
            $user_id = $this->userId;
        }
        $ruleName = str_replace(" ","",$ruleName);
        $user_id = str_replace(" ","",$user_id);
        $result = json_decode(app("ValidateUser")->validateUserPermission($ruleName,$user_id)->getContent(),true);
        if ($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }


    /**
     * 开始直播，将播放列表最早的文件加入到推流任务
     */
    public function liveStart() {
//        $releaseLiveTaskCount = JobsModel::where("queue","PushVideo")->count();
        $releaseLiveTaskCount = $this->redis->llen("{queues:PushVideo}");
        if($releaseLiveTaskCount) {
            return returnAjax(100,"已经有任务在等待执行",false);
        }
        $filePath = PlaylistModel::where("is_delete",0)->order("create_time","ASC")->find();
        $jobClassName  = 'app\common\job\PushVideo';
        $jobQueueName = "PushVideo";
        if($filePath) {
            $addSuccess = Queue::push($jobClassName, $filePath->file_path, $jobQueueName);
            if ($addSuccess) {
                PlaylistModel::where("file_path", $filePath->file_path)
                    ->where("is_delete", 0)
                    ->data(["is_delete" => 1, "update_time" => date("Y-m-d ,H:i:s", time()), "delete_time" => date("Y-m-d ,H:i:s", time())])
                    ->update();
                Log::info($filePath . " 添加到即将播放列表成功");
                return returnAjax(200,"将 ".$filePath->file_name." 添加到推流任务成功",true);
            } else {
                return returnAjax(100,"出现异常 没有将 ".$filePath." 添加到推流任务",false);
            }
        }else {
            return returnAjax(100,"播放列表为空",false);
        }
    }

    /**
     * 将视频添加到播放列表
     * @param Request
     *  video_id 视频id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addVideoToPlaylist()
    {
        $user_id = $this->userId;
        if(!empty($this->requestData["video_id"])) {
            $video_id = $this->requestData["video_id"];
        }else {
            return returnAjax(100,"参数错误：视频id",false);
        }
        $this->requestData["rule_name"] = "video";
        // 验证是否有权限添加视频到播放列表
        $result = json_decode($this->validateUserPermission()->getContent(),true);
        if(!$result["data"]) {
            return returnAjax(100,$result["msg"],false);
        }

        if(!$videoInfo = VideoFileListModel::where("video_status",1)->find($video_id)) {
            return returnAjax(100,"您选择的视频异常",["video_id" => $video_id]);
        }else {
            $filePath = $videoInfo["video_dir"].(!$videoInfo["video_author"]? "" :$videoInfo["video_author"]." - ").$videoInfo["video_name"];
        }
        $fileURL = (new GetDataInMinIO())->getObject($filePath);
        if($fileURL) {
            $releaseTaskResult = json_decode(app("Playlist")->addVideoToPlaylist($filePath,$this->userId)->getContent(),true);
            if($releaseTaskResult["data"]) {
                Log::info("user_id:".$user_id." 点播 video_id:".$video_id);
                return returnAjax(200,"点播完成，等待播放吧",true);
            } else {
                return returnAjax(100,$releaseTaskResult["msg"],false);
            }
        }else {
            Log::error($filePath."视频异常");
            return returnAjax(100,"您选择的视频异常",["video_id" => $video_id]);
        }
    }


    /**
     * 发布当播放列表为空时自动添加一个视频的任务
     */
    public function RandomAddVideoToPlaylist() {
        $result = json_decode(app("Playlist")->RandomAddVideoToPlaylist()->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 编辑用户组权限
     * @param Request
     *  group_id int 用户组id
     *  rules[] array
     *      rules[*] int 规则id
     * @return \type
     */
    public function editPermissionToGroup() {
        $result = json_decode(app("Permission")->editPermissionToGroup()->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],true);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }


    /**
     * 获取用户的权限列表，同时返回用户组列表
     * @param Request
     *  user_id 用户id
     * @return \type
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionListOnUser() {
        $result = json_decode(app("Permission")->getPermissionListOnUser()->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],$result["data"]);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 单独获取 用户组权限
     * @param Request
     *  group_id 用户组id
     * @return \type
     */
    public function getPermissionListOnGroup() {
        $result = json_decode(app("Permission")->getPermissionListOnGroup()->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],$result["data"]);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 单独获取 用户的用户组列表
     * @param Request
     *  user_id 用户id
     * @return \type
     */
    public function getGroupInfoOnUser() {
        $result = json_decode(app("Permission")->getGroupInfoOnUser()->getContent(),true);
        if($result["data"]) {
            return returnAjax(200,$result["msg"],$result["data"]);
        }else {
            return returnAjax(100,$result["msg"],false);
        }
    }

    /**
     * 单独获取权限列表
     * @return \type
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionList() {
        $result = json_decode(app("Permission")->getPermissionList()->getContent(),true);
        return returnAjax(200,$result["msg"],$result["data"]);
    }


    public function updateObject() {
        if(empty($this->requestData["type"])) {
            return returnAjax(100,"类型 不能为空",false);
        }
        if(empty($this->requestData["key"])) {
            $this->requestData["key"] = "/";
        }
//        $data = request()->file("file");
//        $jobClassName = "app\common\job\UploadToMinio";
//        $jobQueueName = "UploadToMinioTask";
//        $jobPushResult = Queue::push($jobClassName,$data,$jobQueueName);
//        if($jobPushResult) {
//            return returnAjax(200,"开始上传",true);
//        }else {
//            return returnAjax(100,"错误",false);
//        }
        if(is_array(request()->file("file"))) {
            foreach (request()->file("file") as $value) {
                $updateObjectResult[] = json_decode(app("UpdateDataToMinIO")->updateObject(fopen($value,"r"),$value->getOriginalName())->getContent(),true)["msg"];
            }
            unset($value);
        }else {
            $updateObjectResult = json_decode(app("UpdateDataToMinIO")->updateObject(request()->file("file"),request()->file("file")->getOriginalName())->getContent(),true)["msg"];
        }
        return returnAjax(200,$updateObjectResult,true);
    }

    /**
     * 删除文件
     * @param Request
     * ID 文件 id
     * @return \type
     */
    public function deleteObject() {
        if(empty($this->requestData["type"])) {
            return returnAjax(100,"类型 不能为空",false);
        }
        $bucket = $this->requestData["type"];
        if(empty($this->requestData[$bucket."_id"])) {
            return returnAjax(100,"文件 id 不能为空",false);
        }
        if("video" == $this->requestData["type"]) {
            $db = new VideoFileListModel();
        }else if("audio" == $this->requestData["type"]) {
            $db = new MusicFileListModel();
        }
        $fileInfo = $db->find($this->requestData[$this->requestData["type"]."_id"]);
        $path = $fileInfo["video_dir"].($fileInfo["video_author"]?$fileInfo["video_author"]." - ":"").$fileInfo["video_name"];
        $deleteObjectResult = json_decode(app("UpdateDataToMinIO")->deleteObject($bucket,$path)->getContent(),true);
        if($deleteObjectResult["data"]) {
            return returnAjax(200,($fileInfo["video_author"]?$fileInfo["video_author"]." - ":"").$fileInfo["video_name"].$deleteObjectResult["msg"],$deleteObjectResult["data"]);
        }else {
            return returnAjax(100,$deleteObjectResult["msg"],$deleteObjectResult["data"]);
        }
    }

    /**
     * 测试方法 无用处
     */
    public function Test() {
        dump(config("queue.default"));
    }
}