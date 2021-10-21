<?php


namespace app\index\controller;
use app\BaseController;
use app\common\service\User as UserService;
use think\exception\ValidateException;

class User extends BaseController
{
    protected $requestData;
    public function initialize() {
        bind("UserService",UserService::class);
        $this->requestData = request()->param();
    }

    /**
     * 验证用户输入是否合法
     * @return \think\response\Json
     */
    protected function validateUser() {
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
        return returnAjax(200,"符合条件",true);
    }

    /**
     * 用户登录
     * @return \think\response\Json
     */
    public function userLogin() {
        $validateUserResult = json_decode($this->validateUser()->getContent(),true);
        if(!$validateUserResult["data"]) {
            return returnAjax(100,$validateUserResult["msg"],false);
        }
        $userLoginResult = json_decode(app("UserService")->userLogin($this->requestData["user_name"],$this->requestData["user_pwd"])->getContent(),true);
        if(!$userLoginResult["data"]) {
            return returnAjax(100,$userLoginResult["msg"],false);
        }else {
            return returnAjax(200,$userLoginResult["msg"],$userLoginResult["data"]);
        }
    }

    /**
     * 创建用户
     * @return \think\response\Json
     */
    public function createUser() {
        $validateUserResult = json_decode($this->validateUser()->getContent(),true);
        if(!$validateUserResult["data"]) {
            return returnAjax(100,$validateUserResult["msg"],false);
        }
        $createuserResult = json_decode(app("UserService")->createUser($this->requestData["user_name"],$this->requestData["user_pwd"])->getContent(),true);
        if(!$createuserResult["data"]) {
            return returnAjax(100,$createuserResult["msg"],false);
        }else {
            return returnAjax(200,$createuserResult["msg"],$createuserResult["data"]);
        }
    }
}