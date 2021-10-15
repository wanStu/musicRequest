<?php


namespace app\common\service;


use app\common\model\MusicFileListModel;
use app\common\model\UserModel;
use app\common\model\VideoFileListModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

/**
 * 从数据库中获取数据
 * Class getDataInDbServer
 * @package app\server
 */
class GetDataInDbServer
{
    public function __construct() {
        $this->requestData = request()->param();
    }
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
        $result = $db->where($type."_status",1)->field("video_id,video_author,video_name,video_dir")->select();
        if($result) {
            return returnAjax(200,$result,true);
        }else {
            if(0 == count($result)) {
                return returnAjax(100,"暂无数据",false);
            }else {
                return returnAjax(100,"意外的错误",false);
            }
        }

    }

    /**
     * 获取用户信息
     * @param $user_id
     * @return \type
     */
    public function getUserInfo($user_id) {
        $userInfoWhere = [];
        if(!empty($user_id)) {
            $userInfoWhere[] = ["user.user_id","=",$user_id];
        }else {
            return returnAjax(100,"参数错误：user_id",false);
        }
        $userInfo =UserModel::join("user_score","user.user_id = user_score.user_id")
            ->join("score_source","user_score.source_id = score_source.source_id")
            ->where($userInfoWhere)
            ->group("user.user_id")
            ->field("user_name,SUM(score) as score")
            ->find();
        return returnAjax(200,"获取成功",$userInfo);
    }

    /**
     * 获取用户分数详情
     * @param $user_id
     * @return \type
     */
    public function getUserScoreInfo($user_id) {
        $userScoreInfoWhere = [];
        if(!empty($user_id)) {
            $userScoreInfoWhere[] = ["user.user_id","=",$user_id];
        }else {
            return returnAjax(100,"参数错误：user_id",false);
        }
        $userInfo =UserModel::join("user_score","user.user_id = user_score.user_id")
            ->join("score_source","user_score.source_id = score_source.source_id")
            ->where($userScoreInfoWhere)
            ->field("user_name,source_name,score,user_score.create_date")
            ->select();
        return returnAjax(200,"获取成功",$userInfo);
    }

}