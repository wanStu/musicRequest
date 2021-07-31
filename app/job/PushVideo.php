<?php


namespace app\job;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use think\queue\Job;
class PushVideo
{
    protected $ffmpeg;
    public function fire(Job $job, $data) {
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }
        $videoUrl = "D:\phpstudy_pro\WWW\musicRequest\public/static/videoFile/自作多情.mp4";
        $isJobDone = $this->liveStart($videoUrl);
        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();
            print("<info>Hello Job has been done and deleted"."</info>\n");
        }else{
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<warn>这个任务已经重试了3次!"."</warn>\n");

                $job->delete();

                // 也可以重新发布这个任务
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
        }



    }
    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        return true;
    }
    /**
     * 根据消息中的数据进行实际的业务处理...
     */
    private function liveStart($videoUrl)
    {
        echo "开始";
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe"
        ]);
        $pushUrl = "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1";
        $video = $ffmpeg->open($videoUrl);
        $format = new X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo "进度 $percentage %",PHP_EOL;
        });
        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $video->save($format, $pushUrl);
        echo "结束";
        return true;
    }
}
//D:\phpstudy_pro\WWW\musicRequest\public/static/ffmpeg/ffmpeg.exe -y -i "D:\phpstudy_pro\WWW\musicRequest\public/static/videoFile/自作多情.mp4" -vcodec copy -acodec aac -f flv  "rtmp://live-push.bilivideo.com/live-bvc/?streamname=live_188609215_9315200&key=ce264338a2392806e0634a40e63df74d&schedule=rtmp&pflag=1"