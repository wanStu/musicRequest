<?php


namespace app\controller;

use think\facade\Queue;
class FfmpegPush
{
    /**php think queue:work --queue
     * 推流到直播间
     */
    public function liveStart() {
        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobHandlerClassName  = 'app\job\PushVideo';

        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName  	  = "PushVideo";

        // 3.当前任务所需的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串
        //   ( jobData 为对象时，存储其public属性的键值对 )
        $jobData       	  = [""] ;
        $isPushed = Queue::push($jobHandlerClassName,$jobData,$jobQueueName);
        if( $isPushed !== false ){
            echo "PushVideo发布完成";
        }else{
            echo 'PushVideo发布失败';
        }
    }
    public function liveClose() {

    }
}