<?php


namespace app\job;

use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use think\queue\Job;
class PushVideo
{
    public function fire(Job $job) {
        $pushUrl = "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1";
        $pushCmd = \FFMpegPush\PushVideo::create();
        $pushCmd->onProgress(function ($percent, $remaining, $rate) {
            echo "进度:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
        });
        try {
            $pushCmd->setInput(
                PushInput::create()
                    ->setStartTime(0)
                    ->setInputVideo(root_path() . "public/static/videoFile/你说江南烟胧雨.mp4")
            )
                ->setFormat(
                    PushFormat::create()
                        ->setVideoCodec(PushFormat::CODE_V_H264)
                )
                ->setOutput(
                    PushOutput::create()
                        ->setPushUrl($pushUrl)
                );
        } catch (\Exception $e) {
            dump($e);
        }
        $pushCmd->push();
        dump($pushCmd->getPushInfo());
    }
}