<?php


namespace app\common\controller;

class Base
{
    const FILE_TYPE = ["video","music"];
    public array $requestData;
    public string $userId;
    public function __construct() {
        $this->requestData = request()->param();
        $this->initialize();
    }
    protected function initialize() {}
}