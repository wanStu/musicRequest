<?php


namespace app\job;


use app\server\PlugFlow;
use Redis;
use app\server\GetDataInDbServer;
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
        $taskNum = $redis->lLen("{queues:PushVideo}");
        $taskReservedNum = $redis->zCard("{queues:PushVideo}:reserved");
//        当{queues:PushVideo}中的值低于2个或{queues:PushVideo}:reserved中没有值时
        if(!$taskReservedNum) {
            for ($i = $redis->zCard("{queues:PushVideo}:reserved");!$i;$i = $redis->zCard("{queues:PushVideo}:reserved")) {
                $fileList = (new GetDataInDbServer)->getFileListInDb("video");
                $index = rand(0,count($fileList)-1);
                if("未知" == $fileList[$index]["video_author"]) {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_name"];
                }else {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"];
                }
                if((new PlugFlow()) -> liveStart($fileFullName)) {
                    echo "播放列表内数量过少，将自动播放 【".$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"]."】",PHP_EOL;
                }
            }
            return true;
        }
        if(!$taskNum) {
            for ($i = $redis->lLen("{queues:PushVideo}");$i < 1;$i = $redis->lLen("{queues:PushVideo}")) {
                $fileList = (new GetDataInDbServer)->getFileListInDb("video");
                $index = rand(0,count($fileList)-1);
                if("未知" == $fileList[$index]["video_author"]) {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_name"];
                }else {
                    $fileFullName = public_path().$fileList[$index]["video_dir"].$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"];
                }
                if((new PlugFlow()) -> liveStart($fileFullName)) {
                    echo "播放列表内数量过少，将自动播放 【".$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"]."】",PHP_EOL;
                }

            }
            return true;
        }
        return false;
    }
}