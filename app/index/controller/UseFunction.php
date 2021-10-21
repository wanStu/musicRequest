<?php


namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\AuthRuleModel;
use app\common\model\MusicFileListModel;
use app\common\model\PlaylistModel;
use app\common\model\VideoFileListModel;
use app\common\service\GetDataInMinIO;
use app\common\service\Permission;
use app\common\service\Playlist;
use app\common\service\UpdateDataToMinIO;
use app\common\service\UserScore;
use app\common\service\ValidateUser;
use app\Request;
use thans\jwt\facade\JWTAuth;
use think\facade\Log;
use think\facade\Queue;
use app\common\service\UserGroup as UserGroupService;
class UseFunction extends Base
{
    protected function initialize() {
        bind("Permission",Permission::class);
        bind("Playlist",Playlist::class);
        bind("ValidateUser",ValidateUser::class);
        bind("UpdateDataToMinIO",UpdateDataToMinIO::class);
        bind("UserGroupService",UserGroupService::class);
        bind("UserScore",UserScore::class);
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionList() {
        $getPermissionListResult = json_decode(app("Permission")->getPermissionList()->getContent(),true);
        if($getPermissionListResult["data"]) {
            return returnAjax(200,$getPermissionListResult["msg"],$getPermissionListResult["data"]);
        }else {
            return returnAjax(100,$getPermissionListResult["msg"],false);
        }
    }

    /**
     * 添加权限
     * @return \think\response\Json
     */
    public function addPermission() {
        if(empty($this->requestData["rule_name"])) {
            return returnAjax(100,"规则标识 不能为空",false);
        }
        if(empty($this->requestData["rule_title"])) {
            return returnAjax(100,"规则名 不能为空",false);
        }
        $addPermissionResult = json_decode(app("Permission")->addPermission($this->requestData["rule_name"],$this->requestData["rule_title"])->getContent(),true);
        if($addPermissionResult["data"]) {
            return returnAjax(200,$addPermissionResult["msg"],true);
        }else {
            return returnAjax(100,$addPermissionResult["msg"],false);
        }
    }

    /**
     * 删除权限
     * @return \think\response\Json
     */
    public function deletePermission() {
        if(empty($this->requestData["rule_id"])) {
            return returnAjax(100,"参数错误",false);
        }
        $deletePermissionResult = json_decode((app("Permission")->deletePermission($this->requestData["rule_id"]))->getContent(),true);
        if($deletePermissionResult["data"]) {
            return returnAjax(200,$deletePermissionResult["msg"],true);
        }else {
            return returnAjax(100,$deletePermissionResult["msg"],false);
        }
    }

    /**
     * 上传文件到minio
     * @return \think\response\Json
     */
    public function updateObject() {
        if(empty($this->requestData["type"])) {
            return returnAjax(100,"类型 不能为空",false);
        }
        if(empty($this->requestData["key"])) {
            $this->requestData["key"] = "/";
        }
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
     * 新建用户组
     * @return \think\response\Json
     */
    public function createUserGroup() {
        if(empty($this->requestData["group_name"]) || empty($this->requestData["rule_id"])) {
            return returnAjax(100,"参数错误",false);
        }
        $rules = AuthRuleModel::where("status",1)->column("id");
        if($ruleDiff = implode(",",array_diff($this->requestData["rule_id"],$rules))) {
            return returnAjax(200,"规则错误：".$ruleDiff,true);
        }
        $ruleId = trim(implode(",",$this->requestData["rule_id"]),",");
        $createUserGroupResult = json_decode((app("UserGroupService")->createUserGroup($this->requestData["group_name"],$ruleId))->getContent(),true);
        if($createUserGroupResult["data"]) {
            return returnAjax(200,$createUserGroupResult["msg"],true);
        }else {
            return returnAjax(100,$createUserGroupResult["msg"],false);
        }
    }

    /**
     * 删除用户组
     * @return \think\response\Json
     */
    public function deleteUserGroup() {
        if(empty($this->requestData["group_id"])) {
            return returnAjax(100,"参数错误",false);
        }
        $deleteUserGroupResult = json_decode((app("UserGroupService")->deleteUserGroup($this->requestData["group_id"]))->getContent(),true);
        if($deleteUserGroupResult["data"]) {
            return returnAjax(200,$deleteUserGroupResult["msg"],true);
        }else {
            return returnAjax(100,$deleteUserGroupResult["msg"],false);
        }
    }

    /**
     * 添加积分来源
     * @return \think\response\Json
     */
    public function addScoreSource() {
        if(empty($this->requestData["source_name"]) || empty($this->requestData["source_detail"]) || empty($this->requestData["score"])) {
            return returnAjax(100,"参数错误",false);
        }
        $addScoreSourceResult = json_decode(app("UserScore")->addScoreSource($this->requestData["source_name"],$this->requestData["source_detail"],$this->requestData["score"])->getContent(),true);
        if(false === $addScoreSourceResult["data"]) {
            return returnAjax(100,$addScoreSourceResult["msg"],false);
        }else {
            return returnAjax(200,$addScoreSourceResult["msg"],true);
        }
    }

    /**
     * 删除积分来源
     * @return \think\response\Json
     */
    public function deleteScoreSource() {
        if(empty($this->requestData["source_id"])) {
            return returnAjax(100,"参数错误",false);
        }
        $deleteScoreSourceResult = json_decode((app("UserScore")->deleteScoreSource($this->requestData["source_id"]))->getContent(),true);
        if($deleteScoreSourceResult["data"]) {
            return returnAjax(200,$deleteScoreSourceResult["msg"],true);
        }else {
            return returnAjax(100,$deleteScoreSourceResult["msg"],false);
        }
    }
    /**
     * 测试方法 无用处
     */
    public function Test() {
        phpinfo();
        dump(config("queue.default"));
    }
}