<?php


namespace app\job;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use think\queue\Job;
class PushVideo
{
    protected $ffmpeg;
    public function fire(Job $job) {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe"
        ]);
        $videoUrl = "D:\phpstudy_pro\WWW\musicRequest\public/static/videoFile/自作多情.mp4";
        $pushUrl = "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1";
        $video = $ffmpeg->open($videoUrl);
        $format = new X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo "进度 $percentage %";
        });

        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $video->save($format, $pushUrl);

    }
}
//D:\phpstudy_pro\WWW\musicRequest\public/static/ffmpeg/ffmpeg.exe -y -i "D:\phpstudy_pro\WWW\musicRequest\public/static/videoFile/自作多情.mp4" -vcodec copy -acodec aac -f flv  "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1"