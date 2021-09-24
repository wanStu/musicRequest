<?php


namespace app\common;
use app\BaseController;
use thans\jwt\exception\JWTException;
use thans\jwt\facade\JWTAuth;

class Base extends BaseController
{
    const FILE_TYPE = ["video","music"];
    public array $requestData;
    public string $userId;
    public function initialize() {
        $this->requestData = request()->param();
        try {
            $this->userId = JWTAuth::auth()["user_id"]->getValue();
        }catch (JWTException $e) {
            $msg = "";
            switch ($e->getMessage()) {
                case "Must have token" :
                    $msg = "请先登录";
                    break;
                case "The token is expired." :
                    $msg = "您的登录已过期";
                    break;
                default :
                    $msg = "您需要先登陆或登陆已过期需要重新登陆";
                    break;
            }
            echo json_encode(['status' => 100, 'msg' => $msg, 'data' => 'false', 'type' => 'json'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}