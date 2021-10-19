<?php


namespace app\common\job;

use app\common\controller\Base;
use app\common\model\JobsModel;
use app\common\model\PlaylistModel;
use app\common\model\VideoFileListModel;
use app\common\service\Playlist;
use app\common\service\GetDataInDbServer;
use think\facade\Queue;
use think\Log;
use think\queue\Job;

class RandomAddVideoToPlaylist extends Base
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
        $this->RandomAddVideoToPlaylist();
    }
    public function RandomAddVideoToPlaylist() {
        $playlistCount = PlaylistModel::where("is_delete",0)->count();
        if($playlistCount < 2) {
            for ($i = $playlistCount;$i < 2;$i++) {
                $fileList = json_decode((new GetDataInDbServer)->getFileListInDb("video")->getContent(),true)["msg"];
                if(0 == count($fileList)) {
                    echo "文件列表为空".PHP_EOL;
                    die();
                }
                $index = rand(0,count($fileList)-1);
                $fileInfo = VideoFileListModel::where("video_status",1)->find($index);
                $filePath = $fileInfo["video_dir"].(("" == $fileInfo["video_author"])? "" :($fileInfo["video_author"]." - ")).$fileInfo["video_name"];
                if(json_decode((new Playlist()) -> addVideoToPlaylist($filePath,0)->getContent(),true)["data"]) {
                    echo "播放列表内数量过少，将自动播放 【".$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"]."】",PHP_EOL;
                    sleep(1);
                }else {
                    if("" == $fileList[$index]["video_author"]) {
                        echo "添加 【".$fileList[$index]["video_name"]."】失败",PHP_EOL;
                    }else {
                        echo "添加 【".$fileList[$index]["video_author"]." - ".$fileList[$index]["video_name"]."】失败",PHP_EOL;
                    }
                }
            }
        }else {
            echo "添加播放列表正常运行".PHP_EOL;
            sleep(1);
        }
        $releaseLiveTaskCount = JobsModel::where("queue","PushVideo")->count();
        if($releaseLiveTaskCount < 1) {
            $filePath = PlaylistModel::where("is_delete",0)->order("create_time","ASC")->find();
            $jobClassName  = 'app\common\job\PushVideo';
            $jobQueueName = "PushVideo";
            if($filePath) {
                $addSuccess = Queue::push($jobClassName, $filePath->file_path, $jobQueueName);
                if ($addSuccess) {
                    PlaylistModel::where("file_path", $filePath->file_path)
                        ->where("is_delete", 0)
                        ->data(["is_delete" => 1, "update_time" => date("Y-m-d ,H:i:s", time()), "delete_time" => date("Y-m-d ,H:i:s", time())])
                        ->update();
                    Log::info($filePath . " 添加到推流任务成功");
                    echo "将 ".$filePath->file_name." 添加到推流任务成功";
                } else {
                    echo "出现异常 没有将 ".$filePath." 添加到推流任务";
                }
            }else {
                echo "播放列表为空".PHP_EOL;
            }
        }else {
            echo "发布推流任务正常运行".PHP_EOL;
        }
    }
}