<?php

namespace app\common\service;

use app\common\model\AuthGroupModel;

class UserGroup
{
    /**
     * 获取用户组列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserGroupList() {
        $getUserGroupListResult = AuthGroupModel::where("status",1)->field("id,title")->select();
        if(false != $getUserGroupListResult) {
            return returnAjax(200,"获取成功",$getUserGroupListResult->toArray());
        }else {
            return returnAjax(100,"获取失败",false);
        }
    }

    /**
     * 创建用户组
     * @param $title
     * @param $ruleId
     */
    public function createUserGroup($title,$ruleId) {
        $groupInfo = AuthGroupModel::where("title",$title)->find();
        if($groupInfo && 1 == $groupInfo["status"]) {
            return returnAjax(100,"创建失败,组名已存在",false);
        }else if($groupInfo) {
            $createGroupResult = $groupInfo->save(["status" => 1,"rules" => $ruleId]);
        }else {
            $createGroupResult = (new AuthGroupModel())->save(["title" => $title,"rules" => $ruleId]);
        }
        if(false != $createGroupResult) {
            return returnAjax(200,"创建成功",true);
        }else {
            return returnAjax(100,"创建失败",false);
        }
    }
    public function deleteUserGroup($id) {
        $groupInfo = AuthGroupModel::where("id",$id)->where("status",1)->find();
        if(!$groupInfo) {
            return returnAjax(100,"删除失败,组不存在",false);
        }
        $deleteUserGroupResult = $groupInfo->save(["status" => 0]);
        if($deleteUserGroupResult) {
            return returnAjax(200,"删除成功",true);
        }else {
            return returnAjax(200,"删除失败",true);
        }
    }
}