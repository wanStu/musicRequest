<?php


namespace app\job;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use think\queue\Job;
class PushVideo
{
    protected $ffmpeg;

    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data) {
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        $job->delete();
        if(!$isJobStillNeedToBeDone){
            return;
        }
        $isJobDone = $this->liveStart($data);
        if ($isJobDone) {
            $job->delete();
        }else{
            if ($job->attempts() > 5) {
                ECHO "这个任务已经重试了5次!",PHP_EOL;
                $job->delete();
            }
        }
    }
    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed $data 将被推流的视频路径
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data): bool
    {
        return true;
    }
    /**
     * 推流视频
     * @param string $videoUrl 将被推流的视频路径
     * @return bool
     */
    private function liveStart(string $videoUrl): bool
    {
        echo "开始播放",PHP_EOL;
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe"
        ]);
        $pushUrl = "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1";
        $video = $ffmpeg->open($videoUrl);
        $format = new X264();
        $format->on('progress', function ($video, $format, $percentage) {
            static $temp = 0;
            if($temp != $percentage) {
                $temp = $percentage;
                echo "播放进度 $percentage %",PHP_EOL;
            }
        });
        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $video->save($format, $pushUrl);
        echo "结束播放",PHP_EOL;
        return false;
    }
}