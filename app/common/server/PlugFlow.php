<?php


namespace app\common\server;
use app\common\Base;
use app\common\model\JobsModel;
use app\common\model\PlayListModel;
use think\facade\Queue;

class PlugFlow extends Base
{
    protected $message;
    protected $error = "未知";
    /**
     * 发布推流到直播间的的任务
     * @param string $filePath 将被推流的视频路径
     */
    public function liveStart(string $filePath,$uid)
    {
        $fileName = explode("/",$filePath)[3];
        $result = PlayListModel::where("is_delete",0)->where("file_path",$filePath)->find();
        if(!$result) {
            $pushSuccess = PlayListModel::create(["file_name" => $fileName,"file_path" => $filePath,"uid" => $uid]);
            if($pushSuccess){
                $this->message = "$fileName 加入列表";
                return returnAjax(200,"$fileName 加入列表",true);
            }else {
                return returnAjax(100,"$fileName 加入列表失败",false);
            }
        }else{
            $this->error = "{$fileName}已经在列表里了";
            return returnAjax(100,"【{$fileName}】已经在列表里了",false);
        }
    }

    /**
     * 发布当播放列表为空时自动添加一个视频的任务
     */
    public function RandomRelease() {
        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobClassName  = 'app\job\RandomRelease';
        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName = "RandomReleaseTask";
        $sum = JobsModel::where("queue","RandomReleaseTask")->count();
        if($sum > 0) {
            $this->error = "已经开启";
            return returnAjax(100,"已经开启",false);
        }
        $pushSuccess = Queue::push($jobClassName, "", $jobQueueName);
        if(false !== $pushSuccess){
            $this->message = "任务 {$jobQueueName} 发布完成";
            return returnAjax(200,"任务 {$jobQueueName} 发布完成",true);
        }else{
            $this->error = "任务 {$jobQueueName} 发布失败";
            return returnAjax(100,"任务 {$jobQueueName} 发布失败",false);
        }
    }

    /**
     * 返回信息
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * 返回错误
     * @return string
     */
    public function getError() {
        return $this->error;
    }
}