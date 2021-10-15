<?php


namespace app\index\controller;
use app\common\service\Login;

class UserLogin
{
    public $login;
    public function __construct(Login $login) {
        $this->login = $login;
    }
    public function index() {
        $result = json_decode($this->login->userLogin()->getContent(),true);
        if(!$result["data"]) {
            return returnAjax(100,$result["msg"],false);
        }else {
            return returnAjax(200,$result["msg"],$result["data"]);
        }
    }
}