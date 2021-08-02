<?php


namespace app\server;
use think\facade\Queue;

class PlugFlow
{
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

        $pushSuccess = Queue::push($jobClassName,$filePath,$jobQueueName);

        if(false !== $pushSuccess){
            return "任务 {$jobQueueName} 发布完成";
        }else{
            return "任务 {$jobQueueName} 发布失败";
        }
    }
}