<?php


namespace app\controller;

use think\facade\Queue;
use app\job\PushVideo;
class FfmpegPush
{
    /**
     * 推流到直播间
     */
    public function pushVideoStart() {
        $job = "PushVideo";
        Queue::push($job);
//        dump($pushCmd->stop());// 停止推流，需要异步调用
    }
}