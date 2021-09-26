<?php


namespace app\index\job;


use app\common\model\JobsModel;
use app\common\model\PlayListModel;
use app\common\model\VideoFileListModel;
use app\common\server\PlugFlow;
use app\common\server\GetDataInDbServer;
use think\facade\Queue;
use think\Log;
use think\queue\Job;

class RandomRelease
{

    public function __construct() {
        $this->requestData = request()->param();
    }
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
        $sum = JobsModel::where("queue","PushVideo")->count();
        if($sum < 1) {
            $filePath = PlayListModel::where("is_delete",0)->order("create_time","ASC")->find();
            $jobClassName  = 'app\job\PushVideo';
            $jobQueueName = "PushVideo";
            if($filePath) {
                echo "将 ".$filePath->file_name." 添加到即将播放";
                $addSuccess = Queue::push($jobClassName,$filePath->file_path,$jobQueueName);
                if($addSuccess) {
                    echo "成功".PHP_EOL;
                    PlayListModel::where("file_path",$filePath->file_path)
                        ->where("is_delete",0)
                        ->data(["is_delete" => 1,"update_time" => date("Y-m-d ,H:i:s",time()),"delete_time" => date("Y-m-d ,H:i:s",time())])
                        ->update();
                    Log::info($filePath." 添加到即将播放列表成功");
                }else {
                    echo "出现异常 没有将 ".$filePath." 添加到即将播放".PHP_EOL;
                }
            }else {
                echo "播放列表为空".PHP_EOL;
            }
        }else {
            echo "发布任务正常运行".PHP_EOL;
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
            }
            return true;
        }else {
            echo "添加播放列表正常运行".PHP_EOL;
            sleep(1);
        }
        return false;
    }
}