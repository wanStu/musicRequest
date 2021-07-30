<?php


namespace app\controller;
use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use FFMpegPush\PushVideo;
use FFMpegPush\PushInfo;

class FfmpegPush
{
    /**
     * @throws \Exception
     */
    public function pushTemp() {
        $pushUrl = "rtmp://";
        $pushCmd = PushVideo::create();
        $pushCmd->onProgress(function ($percent, $remaining, $rate) {
            echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
        });
        $pushCmd->setInput(
            PushInput::create()
                ->setStartTime(0)
                ->setInputVideo(root_path()."public/static/video/test.mp4")
        )
            ->setFormat(
                PushFormat::create()
                    ->setVideoCodec(PushFormat::CODE_V_COPY)
            )
            ->setOutput(
                PushOutput::create()
                    ->setPushUrl($pushUrl)
            );

        echo $pushCmd->getCommandLine();
        $pushCmd->push();

        echo $pushCmd->getErrorOutput();
        echo "\n";
        echo "Exit Code: " . $pushCmd->getExitCode();
//        停止推流，需要异步调用
        echo (new PushInfo())->isSuccessful();
    }
}