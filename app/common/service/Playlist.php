<?php


namespace app\common\service;
use app\common\controller\Base;
use app\common\model\JobsModel;
use app\common\model\PlayListModel;
use think\facade\Queue;

class Playlist extends Base
{
    /**
     * 添加到播放列表
     * @param string $filePath 将被推流的视频路径
     */
    public function addVideoToPlaylist($filePath,$userId)
    {
        $fileNameArr = explode("/",$filePath);
        $fileName = end($fileNameArr);
        $result = PlayListModel::where("is_delete",0)->where("file_path",$filePath)->find();
        if(!$result) {
            $pushSuccess = PlayListModel::create(["file_name" => $fileName,"file_path" => $filePath,"user_id" => $userId]);
            if($pushSuccess){
                return returnAjax(200,"$fileName 加入列表",true);
            }else {
                return returnAjax(100,"$fileName 加入列表失败",false);
            }
        }else{
            return returnAjax(100,"【{$fileName}】已经在列表里了",false);
        }
    }

    /**
     * 发布当播放列表为空时自动添加一个视频的任务
     */
    public function RandomAddVideoToPlaylist() {
        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobClassName  = 'app\common\job\RandomAddVideoToPlaylist';
        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName = "RandomAddVideoToPlaylistTask";
        if("redis" == config("queue.default")) {
            $sum = $this->redis->llen("{queues:RandomAddVideoToPlaylistTask}") + $this->redis->llen("{queues:RandomAddVideoToPlaylistTask}:reserved");
        }else if("database" == config("queue.default")) {
            $sum = JobsModel::where("queue","RandomAddVideoToPlaylistTask")->count();
        }else {
            return returnAjax(100,"任务 {$jobQueueName} 发布失败",false);
        }
        if($sum > 0) {
            return returnAjax(100,"已经开启",false);
        }
        $pushSuccess = Queue::push($jobClassName, "", $jobQueueName);
        if(false !== $pushSuccess){
            return returnAjax(200,"任务 {$jobQueueName} 发布完成",true);
        }else{
            return returnAjax(100,"任务 {$jobQueueName} 发布失败",false);
        }
    }
}