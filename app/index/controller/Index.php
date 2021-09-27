<?php


namespace app\index\controller;

class Index
{
    /**
     * 主页
     * @return string
     */
    public function index()
    {
        $system = php_uname();
        echo $system,PHP_EOL;
        if(strstr($system,"Windows")) {
        }else if(strstr($system,"Linux")){
            echo "Linux 环境，暂未开发",PHP_EOL;
        }else {
            echo "其他 环境，暂未开发",PHP_EOL;
        }
        return returnAjax(200,app()->getRootPath(),true);
    }
}