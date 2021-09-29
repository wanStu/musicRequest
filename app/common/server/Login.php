<?php


namespace app\common\server;

use app\BaseController;
use app\common\model\UserScoreModel;
use thans\jwt\facade\JWTAuth;
use think\exception\ValidateException;

class Login extends BaseController
{
    public array $requestData;
    public function initialize() {
        $this->requestData = request()->param();
    }
    public function userLogin() {
        $password = "";
        $rule = [
            "user_name" => "require|max:20",
            "user_pwd"  => "require"
        ];
        $msg = [
            "user_name.require" => "用户名不能为空",
            "user_pwd.require"  => "用户密码不能为空",
            "user_name.max" => "用户名异常",
        ];
        try {
            $this->validate($this->requestData,$rule,$msg);
        } catch (ValidateException $e){
            return returnAjax(100,$e->getError(),false);
        }
        if(!(UserScoreModel::where("user_name",$this->requestData["user_name"])->count())) {
            return returnAjax(100,"用户名或密码错误",false);
        }else {
            $password = hash("sha256",config("app.password_key").md5($this->requestData["user_pwd"]));
        }
        if($userId = UserScoreModel::where("user_name",$this->requestData["user_name"])->where("user_pwd",$password)->field("user_id")->find()) {
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
}