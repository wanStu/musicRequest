<?php


namespace app\job;


use app\model\PlayListModel;
use app\server\PlugFlow;
use Redis;
use app\server\GetDataInDbServer;
use think\facade\Queue;
use think\Log;
use think\queue\Job;

class RandomRelease
{
    /**
     * @param Job $job
     * @param $data
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fire(Job $job,$data) {
        $this->randomRelease();
    }
    public function randomRelease() {
        $redis = new Redis();
        $redis -> connect("127.0.0.1");
        $sum = $redis->lLen("{queues:PushVideo}") + $redis->zCard("{queues:PushVideo}:reserved");
        if($sum == 0) {
            $filePath = PlayListModel::where("is_delete",0)->order("create_time","ASC")->find();
            $jobClassName  = 'app\job\PushVideo';
            $jobQueueName = "PushVideo";
            if($filePath) {
                echo "将 ".$filePath->file_name." 添加到待播放";
                $addSuccess = Queue::push($jobClassName,$filePath->file_path,$jobQueueName);
                if($addSuccess) {
                    echo "成功".PHP_EOL;
                    PlayListModel::where("file_path",$filePath->file_path)
                        ->where("is_delete",0)
                        ->data(["is_delete" => 1,"update_time" => date("Y-m-d ,H:i:s",time())])
                        ->update();
                    Log::info($filePath." 添加到播放列表成功");
                }
            }else {
                echo "出现异常 没有将 ".$filePath." 添加到待播放".PHP_EOL;
            }
        }
        $playlistCount = PlayListModel::where("is_delete",0)->count();
        if($playlistCount < 2) {
            for ($i = $playlistCount;$i < 2;$i++) {
                $fileList = json_decode((new GetDataInDbServer)->getFileListInDb("video")->getContent(),true)["msg"];
                $index = rand(0,count($fileList)-1);
                if("未知" == $fileList[$index]["video_author"]) {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_name"];
                }else {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"];
                }
                if(json_decode((new PlugFlow()) -> liveStart($fileFullName,0)->getContent(),true)["data"]) {
                    echo "播放列表内数量过少，将自动播放 【".$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"]."】",PHP_EOL;
                }
                sleep(1);
            }
            return true;
        }else {
            sleep(1);
        }
        return false;
    }
}