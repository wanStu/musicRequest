<?php


namespace app\common\controller;

use Redis;

class Base
{
    const FILE_TYPE = ["video","music"];
    public array $requestData;
    public string $userId;
    public Redis $redis;
    public function __construct() {
        $this->requestData = request()->param();
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->initialize();

    }
    protected function initialize() {}
}