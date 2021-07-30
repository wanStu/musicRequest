<?php


namespace app\server;
use app\model\ThinkAuthRuleModel;
use app\model\VideoFileListModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 更新数据库中的数据
 * Class UpdateFileDataToDbServer
 * @package app\server
 */
class UpdateFileDataToDbServer
{
    /**
     * 获得静态文件夹中的文件列表
     * @param string $type 类型 [music/video]
     * @param string $dir  歌曲文件路径（绝对路径）
     * @var string $dir 文件路径，默认为 （app()->getRootPath()."/public/static/<music/video>File"）
     * @var array $fileList 文件列表
     * @return array|string $fileList|“文件夹有误” 返回歌曲文件列表|错误信息
     * $fileList = [
     *      序号 => 文件名,
     *      ......
     *      子目录名 => [
     *          序号 => 文件名,
     *          .....
     *      ]
     * ]
     */
    public function getList(string $type,string $dir = "") {
        $dir = ($dir != "") ? $dir : app()->getRootPath()."public/static/".$type."File";

        if(!$fileList = scandir($dir)) {
            return "文件夹路径有误";
        }
        foreach ($fileList as $key => $item) {
            if(is_dir($dir."/".$item)) {
                unset($fileList[$key]);
                if($item == ".." || $item == ".") {
                    continue;
                }
                $fileList[$item] = $this -> getList($type,$dir."/".$item);
            }
        }
        return $fileList;
    }

    /**
     * 将从本地读取的歌曲文件信息更新到数据库
     * @param string $type 类型 [music/video]
     * @param array $fileList 本地歌曲文件列表，默认是 Index::getMusicList() 的返回值
     * $fileList = [
     *      序号 => 文件名,
     *      ......
     *      子目录名 => [
     *          序号 => 文件名,
     *          ......
     *      ]
     * ]
     * @param string $dir 文件相对路径(相对于 app()->getRootPath()."/public/static/<music/video>File/") 默认为空
     * @return array $msg 返回上传情况
     * $msg = [
     *      error => [
     *          序号 => 信息,
     *          ......
     *      ],
     *      success => [
     *           序号 => 信息,
     *           ......
     *      ],
     *      info => [
     *          序号 => 信息,
     *           ......
     *      ]
     * ]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateDb(string $type, array $fileList = [], string $dir = ""): array
    {
        $msg =[
            "error" => [],
            "success"   => [],
            "info" => []
        ];
        $db;
        if (($fileList == [])) {
            $fileList = (new UpdateFileDataToDbServer) -> getList($type);
        }
        foreach ($fileList as $key => $item) {
            if(is_dir(app()->getRootPath()."public/static/".$type."File/".$key)) {
                $msg = array_merge_recursive($msg,$this -> updateDb($type,$item,$key."/"));
                continue;
            }
            $fileInfo = explode("-",$item,2);
            $fileInfo[] = "/static/".$type."File/{$dir}";
            $data[$type."_author"] = trim($fileInfo[0]);
            $data[$type."_name"] = trim($fileInfo[1]);
            $data[$type."_dir"] = $fileInfo[2];
            if($type == "music") {
                $db = new ThinkAuthRuleModel;
            }else if($type == "video") {
                $db = new VideoFileListModel;
            }
            if(!$db->where($type."_name",$data[$type."_name"])->where($type."_author",$data[$type."_author"])->find()) {
                if(!$db::create($data)) {
                    $msg["error"][] = $data[$type."_author"] . " - " . $data[$type."_name"] . "添加失败";
                }else {
                    $msg["success"][] = $data[$type."_author"] . " - " . $data[$type."_name"] . " 添加成功";
                }
            }else {
                $msg["info"][] = $data[$type."_author"]." - ".$data[$type."_name"]." 已存在";
            }
        }
        return $msg;
    }
    /**
     * 更新数据库中状态非 -1(禁用) 的文件状态 若能在本地找到则状态为 1(正常) 找不到状态为 0(找不到资源)
     * @param string $type 类型 [music/video]
     * @return array|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateFileStatusInDb(string $type = ""){
        $msg = [];
        $db;
        if($type == "music") {
            $db = new ThinkAuthRuleModel;
            $fileList = $db->where("music_status","<>",-1)->select();
        }else if($type == "video"){
            $db = new VideoFileListModel;
            $fileList =$db->where("video_status","<>",-1)->select();
        }else {
            return "类型错误";
        }
        foreach ($fileList as $item) {
            $fullFileName = $item[$type."_dir"].$item[$type."_author"]." - ".$item[$type."_name"];
            if(file_exists(app()->getRootPath()."public". $fullFileName)) {$db::update([$type."_status" => 0],[$type."_id" => $item[$type."_id"]]);
                $db::update([$type."_status" => 1],[$type."_id" => $item[$type."_id"]]);
                $msg["find"][] =  "【".$fullFileName."】"." 可以找到，状态修改为 1 ";
            } else {
                $db::update([$type."_status" => 0],[$type."_id" => $item[$type."_id"]]);
                $msg["notFound"][] =  "【".$fullFileName."】"." 文件找不到，状态修改为 0 ";
            }
        }
        return $msg;
    }
}