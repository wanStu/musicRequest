<?php

namespace app\common\service;

use app\BaseController;
use app\common\model\AuthGroupAccessModel;
use app\common\model\UserModel;
use thans\jwt\facade\JWTAuth;

class User extends BaseController
{
    public array $requestData;
    public function initialize() {
        $this->requestData = request()->param();
    }

    /**
     * 用户登录
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userLogin($userName,$userPwd) {
        if(!(UserModel::where("user_name",$userName)->count())) {
            return returnAjax(100,"用户名或密码错误",false);
        }else {
            $password = hash("sha256",config("app.password_key").md5($userPwd));
        }
        if($userId = UserModel::where("user_name",$userName)->where("user_pwd",$password)->field("user_id")->find()) {
            $userId = $userId->toArray();
        }
        if(!$userId) {
            return returnAjax(100,"用户名或密码错误",false);
        }else {
            $data["token"] = JWTAuth::builder(["user_id" => $userId["user_id"]]);
            cookie("token",$data["token"],86400);
            return returnAjax(200,"登陆成功",$data);
        }

    }

    /**
     * 创建用户
     */
    public function createUser($userName,$userPwd) {
        if((UserModel::where("user_name",$userName)->count())) {
            return returnAjax(100,"用户名已存在",false);
        }else {
            $password = hash("sha256",config("app.password_key").md5($userPwd));
        }
        $createUserResult = (new UserModel())->save(["user_name" => $userName,"user_pwd" => $password,"create_data" => date("Y/m/d H:i:s",time())]);
        if($createUserResult) {
            return returnAjax(200,"创建用户【".$userName."】成功",true);
        }else {
            return returnAjax(100,"创建用户【".$userName."】失败",false);
        }
    }

    /**
     * 获取用户信息
     * @param $userId
     */
    public function getUserInfo($userId) {
        $userInfoWhere = [];
        if(!empty($userId)) {
            $userInfoWhere[] = ["user.user_id","=",$userId];
        }else {
            return returnAjax(100,"参数错误：user_id",false);
        }
        $userInfo =UserModel::leftJoin("user_score","user.user_id = user_score.user_id")
            ->leftJoin("score_source","user_score.source_id = score_source.source_id")
            ->where($userInfoWhere)
            ->group("user.user_id")
            ->field("user_name,SUM(score) as score")
            ->find();
        return returnAjax(200,"获取成功",$userInfo);
    }

    /**
     * 获取用户分数详情
     * @param $userId
     */
    public function getUserScoreInfo($userId) {
        $userScoreInfoWhere = [];
        if(!empty($userId)) {
            $userScoreInfoWhere[] = ["user.user_id","=",$userId];
        }else {
            return returnAjax(100,"参数错误：user_id",false);
        }
        $userInfo =UserModel::leftJoin("user_score","user.user_id = user_score.user_id")
            ->leftJoin("score_source","user_score.source_id = score_source.source_id")
            ->where($userScoreInfoWhere)
            ->field("user_name,source_name,score,user_score.create_date")
            ->select();
        return returnAjax(200,"获取成功",$userInfo);
    }

    public function addUserGroupToUser($userId,$userGroupId) {
        $userGroupInfo = AuthGroupAccessModel::where("uid",$userId)->column("group_id");
        if(in_array($userGroupId,$userGroupInfo)) {
            return returnAjax(100,"添加失败，已经在该用户组中",false);
        }
        $addUserGroupToUserResult = (new AuthGroupAccessModel())->save(["uid" => $userId,"group_id" => $userGroupId]);
        if($addUserGroupToUserResult) {
            return returnAjax(200,"添加成功",true);
        }else {
            return returnAjax(100,"添加失败",false);
        }
    }

}