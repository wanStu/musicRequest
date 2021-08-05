<?php


namespace app\server;
use Redis;
use think\facade\Queue;

class PlugFlow
{
    protected $message;
    protected $error = "未知";
    /**
     * 发布推流到直播间的的任务
     * @param string $filePath 将被推流的视频路径
     */
    public function liveStart(string $filePath): string
    {
        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobClassName  = 'app\job\PushVideo';

        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName = "PushVideo";

        $redis = new Redis();
        $redis -> connect("127.0.0.1");
        $taskNum = $redis->lLen("{queues:PushVideo}");
        $taskReservedNum = $redis->zCard("{queues:PushVideo}:reserved");
        if($taskReservedNum) {
            $taskReservedList = $redis->zRange("{queues:PushVideo}:reserved",0,-1);
            $taskReserved = json_decode($taskReservedList[0],true);
            if ($taskReserved["data"] == $filePath) {
                $this->error = "该视频已经在列表里了";
                return false;
            }
        }
        for($i = $taskNum;$i--;){
            $temp = json_decode($redis->lIndex("{queues:PushVideo}",$i),true);
            if($temp["data"] == $filePath) {
                $this->error = "该视频已经在列表里了";
                return false;
            }
        }
        $pushSuccess = Queue::push($jobClassName,$filePath,$jobQueueName);
        if(false !== $pushSuccess){
            $this->message = "任务 {$jobQueueName} 发布完成";
            return true;
        }else{
            $this->message = "任务 {$jobQueueName} 发布失败";
            return false;
        }
    }
    public function getError() {
        return $this->error;
    }
}