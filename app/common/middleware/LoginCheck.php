<?php


namespace app\common\middleware;
use Exception;
use thans\jwt\facade\JWTAuth;

class LoginCheck
{
    public function handle($request,\Closure $next) {
        try {
            JWTAuth::auth();
        }catch (Exception $e){
            echo json_encode(['status' => 100, 'msg' => "登陆异常，请重新登录", 'data' => 'false', 'type' => 'json'], JSON_UNESCAPED_UNICODE);
            die();
        }
        return $next($request);
    }
}