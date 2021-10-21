<?php


namespace app\common\service;
use app\common\controller\Base;
use app\common\model\AuthGroupAccessModel;
use app\common\model\AuthGroupModel;
use app\common\model\AuthRuleModel;
use app\common\model\UserScoreModel;
use app\Request;

class Permission extends Base
{

    /**
     * 编辑 用户组权限
     * @param Request
     *  group_id int 用户组id
     *  rules[] array
     *      rules[*] int 规则id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editPermissionToGroup() {
        //获取用户组id
        if(!empty($this->requestData["group_id"]) && is_numeric($this->requestData["group_id"])) {
            $groupInfo = AuthGroupModel::where("id","=",$this->requestData["group_id"])->find();
            if(!$groupInfo) {
                return returnAjax(100,"参数错误：权限id不存在",false);
            }
        }else {
            return returnAjax(100,"参数错误：用户组id",false);
        }
        //获取要设置的权限
        if(!empty($this->requestData["rules"])) {
            if(is_array($this->requestData["rules"])) {
                $rules = array_unique($this->requestData["rules"]);
                //判断权限是否存在
                foreach ($rules as $key => $value) {
                    $result = AuthRuleModel::where("id","=",$value)->count();
                    if(!$result) {
                        return returnAjax(100,"参数错误：权限id 【".$value."】 不存在",false);
                    }
                }
                unset($key);
                unset($value);
                $rules = implode(",",$rules);
            }else {
                $rules = $this->requestData["rules"];
            }
        }else {
            return returnAjax(100,"参数错误：权限id",false);
        }

        //判断权限更改
        if($rules == $groupInfo->rules) {
            return returnAjax(100,"权限跟之前没有区别,不进行更新",false);
        }
        // 设置权限
        $groupInfo->rules = $rules;
        $result = $groupInfo->save();
        if(!$result) {
            return returnAjax(100,"编辑失败",false);
        }else {
            return returnAjax(200,"编辑成功",true);
        }
    }

    /**
     * 获取权限列表
     * @param Request
     *  group_id int 用户组id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionListOnGroup() {
        //获取用户组id
        if(!empty($this->requestData["group_id"]) && is_numeric($this->requestData["group_id"])) {
            $groupInfo = AuthGroupModel::where("id","=",$this->requestData["group_id"])->find();
            if(!$groupInfo) {
                return returnAjax(100,"参数错误：用户组id 【".$this->requestData["group_id"]."】 不存在",false);
            }
        }else if(!empty($groupId)){
            $groupInfo = AuthGroupModel::where("id","=",$groupId)->find();
            if(!$groupInfo) {
                return returnAjax(100,"参数错误：用户组id 【".$groupId."】 不存在",false);
            }
        }else{
            return returnAjax(100,"参数错误：用户组id",false);
        }
        $rules = explode(",",$groupInfo->rules);
        foreach ($rules as $key => $value) {
            $data["has"][] = AuthRuleModel::field("id,title,status,type")->where("id",$value)->find();
        }
        unset($key);
        unset($value);
        $data["all"] = AuthRuleModel::field("id,title,status,type")->select();
        return returnAjax(200,"获取成功",$data);
    }

    /**
     * 获取权限列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionList() {
        $getPermissionListResult = AuthRuleModel::where("status",1)->field("id,title,type")->select();
        if(false === $getPermissionListResult) {
            return returnAjax(100,"获取失败",false);
        }else {
            return returnAjax(200,"获取成功",$getPermissionListResult);
        }
    }

    /**
     * 添加权限
     * @param $title
     * @param int $type
     * @param string $condition
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addPermission($name,$title,$type = 1,$condition = "") {
        $ruleInfo = AuthRuleModel::where("name",$name)->find();
        if($ruleInfo && 1 == $ruleInfo["status"]) {
            return returnAjax(100,"创建失败,规则名已存在",false);
        }else if($ruleInfo) {
            $addPermissionResult = $ruleInfo->save(["title" => $title,"statu s" => 1,"type" => $type,"condition" => $condition]);
        }else {
            $addPermissionResult = (new AuthRuleModel())->save(["name" => $name,"title" => $title,"type" => $type,"condition" => $condition]);
        }
        if(false != $addPermissionResult) {
            return returnAjax(200,"创建成功",true);
        }else {
            return returnAjax(100,"创建失败",false);
        }
    }

    /**
     * 删除权限
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deletePermission($id) {
        $ruleInfo = AuthRuleModel::where("id",$id)->where("status",1)->find();
        if(!$ruleInfo) {
            return returnAjax(100,"删除失败,规则不存在",false);
        }
        $deletePermissionResult = $ruleInfo->save(["status" => 0]);
        if($deletePermissionResult) {
            return returnAjax(200,"删除成功",true);
        }else {
            return returnAjax(200,"删除失败",true);
        }
    }
    /**
     * 获取 用户权限列表
     * @param Request
     *  user_id 用户id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionListOnUser() {
        //获取用户id
        if(!empty($this->requestData["user_id"]) && is_numeric($this->requestData["user_id"])) {
            $UserInfo = UserScoreModel::where("user_id","=",$this->requestData["user_id"])->find();
            if(!$UserInfo) {
                return returnAjax(100,"参数错误：用户id不存在",false);
            }
        }else {
            return returnAjax(100,"参数错误：用户id",false);
        }
        // 获取 用户 所在用户组id
        $groupId = AuthGroupAccessModel::where("uid","=",$this->requestData["user_id"])->column("group_id");
        if(!count($groupId)) {
            return returnAjax(200,"没有分配用户组",true);
        }
        //获取 对应用户组 的权限规则
        $rules = [];
        foreach ($groupId as $key => $value) {
            $rules[] = json_decode($this->getPermissionListOnGroup($value)->getContent(),true);
            if(100 == $rules[$key]["status"]){
                return returnAjax(100,"用户所在组 【".$value."】 异常",false);
            }
        }
        unset($key);
        unset($value);
        $temp = $rules[0];
        foreach ($rules as $key => $value) {
            $temp["data"]["has"] = array_merge_recursive($value["data"]["has"],$temp["data"]["has"]);
        }
        unset($key);
        unset($value);
//        去除重复项
        for ($i = 0;$i <  count($temp["data"]["has"]);$i++) {
            for ($j = 1;$j <  count($temp["data"]["has"]);$j++) {
                if($temp["data"]["has"][$i]["id"] == $temp["data"]["has"][$j]["id"]) {
                    unset($temp["data"]["has"][$j]);
                    $temp["data"]["has"] = array_values($temp["data"]["has"]);
                }
            }
        }
//        排序
        array_multisort($temp["data"]["has"]);
        $temp["data"]["groupList"] = $groupId;
        return returnAjax($temp["status"],$temp["msg"],$temp["data"]);
    }

    /**
     * 单独获取 用户的用户组列表
     * @param Request
     *  user_id 用户id
     */
    public function getGroupInfoOnUser() {
        //获取用户id
        if(!empty($this->requestData["user_id"]) && is_numeric($this->requestData["user_id"])) {
            $UserInfo = UserScoreModel::where("user_id","=",$this->requestData["user_id"])->find();
            if(!$UserInfo) {
                return returnAjax(100,"参数错误：用户id不存在",false);
            }
        }else {
            return returnAjax(100,"参数错误：用户id",false);
        }
        // 获取 用户 所在用户组id
        $groupId = AuthGroupAccessModel::where("uid","=",$this->requestData["user_id"])->column("group_id");
        $groupInfo = [];
        foreach ($groupId as $key => $value) {
            if($groupInfo[$key] = AuthGroupModel::where("id",$value)->field("id,title")->find()) {
                $groupInfo[$key] = $groupInfo[$key]->toArray();
            }
        }
        if(!count($groupInfo)) {
            return returnAjax(200,"没有分配用户组",true);
        }
        return returnAjax(200,"获取成功",$groupInfo);
    }
}