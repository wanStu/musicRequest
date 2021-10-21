<?php

namespace app\common\service;

use app\common\model\AuthGroupModel;
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

    public function addScoreSource($sourceName,$sourceDetail,$score) {
        $scoreSourceInfo = ScoreSourceModel::where("source_name",$sourceName)->find();
        if($scoreSourceInfo && 1 == $scoreSourceInfo["source_status"]) {
            return returnAjax(100,"添加失败【".$sourceName."】已经存在",false);
        }else if($scoreSourceInfo) {
            $addScoreSourceResult = $scoreSourceInfo->save(["source_detail" => $sourceDetail,"score" => $score,"source_status" => 1]);
        }else {
            $addScoreSourceResult = (new ScoreSourceModel())->save(["source_name" => $sourceName,"source_detail" => $sourceDetail,"score" => $score]);
        }
        if($addScoreSourceResult) {
            return returnAjax(200,"添加成功",true);
        }else {
            return returnAjax(100,"添加失败",false);
        }
    }

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
            return returnAjax(200,"删除成功",true);
        }else {
            return returnAjax(200,"删除失败",true);
        }
    }
}