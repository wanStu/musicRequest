<?php


namespace app\common\job;


use app\common\model\LiveServerModel;
use app\common\service\GetDataInMinIO;
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
        $system = php_uname();
        if(stristr($system,"Windows")) {
            echo "Windows 环境",PHP_EOL;
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
                'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe",
                'timeout'          => 600
            ]);
        }else if(stristr($system,"Linux")){
            echo "Linux 环境",PHP_EOL;
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => "ffmpeg",
                'ffprobe.binaries' => "ffprobe",
                'timeout'          => 600
            ]);
        }else {
            echo "其他 环境",PHP_EOL;
            die("暂未开发");
        }
        $server = LiveServerModel::find(1)->toArray();
        $pushPath = $server["server_path"].$server["server_key"];
        $fileFullUrl = (new GetDataInMinIO())->getObject($videoUrl);
        $fileUrl = "http://127.0.0.1:9000".strstr($fileFullUrl,"/v");
        $video = $ffmpeg->open($fileUrl);
        //  参数
        $pushVideo = new X264();
        $pushVideo->setKiloBitrate(0)
            ->setInitialParameters(["-re"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        echo "开始播放 ".$videoUrl,PHP_EOL;
        if($job->attempts() > 3) {
            $job->delete();
        }
        $pushVideo->on('progress', function ($audio, $format, $percentage) {
            static $percentageCopy = 0;
            if($percentage != $percentageCopy) {
                $percentageCopy = $percentage;
                echo "进度 {$percentage} % ",PHP_EOL;
            }
        });
        $video->save($pushVideo, $pushPath);
        echo "播放 ".$videoUrl." 结束",PHP_EOL;
        return true;
    }
}