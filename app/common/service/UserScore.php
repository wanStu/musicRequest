<?php

namespace app\common\service;
use app\common\model\ScoreSourceModel;
use app\common\model\UserScoreModel;

class UserScore
{


    /**
     * 获取积分来源列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getScoreSourceList() {
        $getScoreSourceListResult = ScoreSourceModel::where("source_status",1)->field("source_id,source_name,source_detail,score")->select();
        if(false !== $getScoreSourceListResult) {
            return returnAjax(200,"获取成功",$getScoreSourceListResult);
        }else {
            return returnAjax(100,"获取失败",false);
        }
    }

    /**
     * 添加积分来源
     * @param $sourceName
     * @param $sourceDetail
     * @param $score
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addScoreSource($sourceName,$sourceDetail,$score) {
        $scoreSourceInfo = ScoreSourceModel::where("source_name",$sourceName)->find();
        if($scoreSourceInfo && 1 == $scoreSourceInfo["source_status"]) {
            return returnAjax(100,"添加失败【".$sourceName."】已经存在",false);
        }else if($scoreSourceInfo) {
            $addScoreSourceResult = $scoreSourceInfo->save(["source_detail" => $sourceDetail,"score" => $score,"source_status" => 1,"update_data" => date("Y/m/d H:i:s",time())]);
        }else {
            $addScoreSourceResult = (new ScoreSourceModel())->save(["source_name" => $sourceName,"source_detail" => $sourceDetail,"score" => $score,"create_data" => date("Y/m/d H:i:s",time())]);
        }
        if($addScoreSourceResult) {
            return returnAjax(200,"添加成功",true);
        }else {
            return returnAjax(100,"添加失败",false);
        }
    }

    /**
     * 删除积分来源
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteScoreSource($id) {
        $scoreSourceInfo = ScoreSourceModel::where("source_id",$id)->where("is_delete",0)->find();
        if(!$scoreSourceInfo) {
            return returnAjax(100,"删除失败,该积分来源不存在",false);
        }
        $deleteScoreSourceResult = $scoreSourceInfo->save(["is_delete" => 1,"delete_date" => date("Y/m/d H:i:s",time()),"update_date" => date("Y/m/d H:i:s",time())]);
        if($deleteScoreSourceResult) {
            return returnAjax(200,"删除成功",true);
        }else {
            return returnAjax(200,"删除失败",true);
        }
    }

    public function editScoreSource($data) {
        $scoreSourceInfo = ScoreSourceModel::where("id",$data["id"])->where("is_delete",0)->find();
        if(!$scoreSourceInfo) {
            return returnAjax(100,"编辑失败,该积分来源不存在",false);
        }
        $deleteScoreSourceResult = $scoreSourceInfo->save($data);
        if($deleteScoreSourceResult) {
            return returnAjax(200,"编辑成功",true);
        }else {
            return returnAjax(200,"编辑失败",true);
        }
    }

    public function addUserScore($user_id,$source_id) {
        $day = date("Y-m-d");
        if(1 == $source_id && UserScoreModel::where("user_id",$user_id)->where("source_id",1)->where("create_date","like","%".$day."%")->count()) {
            return returnAjax(100,"今天已经签到过了，添加积分失败！",false);
        }
        $addUserScoreResult = (new UserScoreModel())->save(["user_id" => $user_id,"source_id" => $source_id,"create_data" => date("Y/m/d H:i:s",time())]);
        if(false === $addUserScoreResult) {
            return returnAjax(100,"添加积分失败",false);
        }else {
            return returnAjax(200,"添加积分成功",true);
        }
    }
}