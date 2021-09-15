<?php


namespace app\job;


use app\model\PlayListModel;
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
        $jobDone = $this->pushStart($job,$data);
        if($jobDone) {
            $job->delete();
        }

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
        if ($job->attempts() > 2) {
            echo  "5s后开始第".$job->attempts()."次执行！",PHP_EOL,"将删除任务并最后执行一次";
            $job->delete();
        }else {
            echo "5s后开始第".$job->attempts()."次执行！",PHP_EOL;
        }
        sleep(5);
        $video->save($format, $pushPath);
        return true;
    }
}