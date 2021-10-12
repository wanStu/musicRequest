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
        return returnAjax(200,app()->getRootPath(),true);
    }
}