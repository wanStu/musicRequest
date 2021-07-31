<?php


namespace app\controller;

use think\facade\Queue;
use FFmpeg;
class FfmpegPush
{
    /**
     * 推流到直播间
     */
    public function liveStart() {
        $job = "PushVideo";
        Queue::push($job);
    }
    public function liveClose() {
    }
}