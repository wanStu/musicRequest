<?php


namespace app\job;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use think\queue\Job;

class PushVideo
{
    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data) {
        $job->delete();
        $jobDone = $this->pushStart($job,$data);
        return true;
    }
    /**
     * 推流视频
     * @param string $videoUrl 将被推流的视频路径
     * @return bool
     */
    private function pushStart(Job $job,string $videoUrl): bool
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe",
            'timeout'          => 360
        ]);

        $pushPath = "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1";
        $video = $ffmpeg->open($videoUrl);
        $format = new X264();
        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $fileInfo = explode("/",$videoUrl);
        echo "开始播放 ".$fileInfo[count($fileInfo)-1],PHP_EOL;
        $format->on('progress', function ($audio, $format, $percentage) {
            static $percentageCopy = 0;
            if($percentage != $percentageCopy) {
                $percentageCopy = $percentage;
                echo "进度 {$percentage} % ",PHP_EOL;
                if(49 < $percentageCopy) {
                   die();
                }
            }
        });
        $video->save($format, $pushPath);
        return true;
    }
}