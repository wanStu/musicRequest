<?php


namespace app\job;

use FFMpeg\FFMpeg;
use think\queue\Job;
class PushVideo
{
    public function fire(Job $job) {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe",
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ]);

    }
}
//root_path() . "public/static/videoFile/你说江南烟胧雨.mp4"
//rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1