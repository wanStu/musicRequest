<?php


namespace app\common\service;
use app\common\controller\Base;
use app\common\model\MusicFileListModel;
use app\common\model\VideoFileListModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 更新数据库中的数据
 * Class UpdateFileInfoToDbServer
 * @package app\server
 */
class UpdateFileInfoToDbServer extends Base
{
    /**
     * 获得静态文件夹中的文件列表
     * @param string $type 类型 [music/video]
     * @param string $dir  歌曲文件路径（绝对路径）
     * @var string $dir 文件路径，默认为 （app()->getRootPath()."/public/static/<music/video>File"）
     * @var array $fileList 文件列表
     * $fileList = [
     *      序号 => 文件名,
     *      ......
     *      子目录名 => [
     *          序号 => 文件名,
     *          .....
     *      ]
     * ]
     */
    public function getFileListInlocal(string $type,string $dir = "") {
        $dir = ( "" != $dir) ? $dir : app()->getRootPath()."public/static/".$type."File";

        if(!$fileList = scandir($dir)) {
            return "文件夹路径有误";
        }
        foreach ($fileList as $key => $item) {
            if(is_dir($dir."/".$item)) {
                unset($fileList[$key]);
                if(".." == $item || "." == $item) {
                    continue;
                }
                $fileList[$item] = json_decode($this -> getFileListInlocal($type,$dir."/".$item)->getContent(),true);
            }
        }
        return returnAjax(200,$fileList,true);
    }

    /**
     * 将从本地读取的文件信息更新到数据库
     * @param string $type 类型 [music/video]
     * @param array $fileList 本地文件列表，默认是 $this -> getFileListInlocal() 的返回值
     * $fileList = [
     *      序号 => 文件名,
     *      ......
     *      子目录名 => [
     *          序号 => 文件名,
     *          ......
     *      ]
     * ]
     * @param string $dir 文件相对路径(相对于 app()->getRootPath()."/public/static/<music/video>File/") 默认为空
     * @return mixed $msg 返回上传情况
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
    public function updateFileListToDb(string $type, array $fileList = [])
    {
        $msg =[
            "error" => [],
            "success"   => [],
            "info" => []
        ];
        if (([] == $fileList )) {
            $fileList = json_decode((new GetDataInMinIO())->getObjectList($type)->getContent(),true)["data"];
        }
        if(false === $fileList){
            $fileList = [];
        }else if(!is_array($fileList)) {
            $fileList = [$fileList];
        }
        foreach ($fileList as $item) {
            $fileDir = !empty(explode("/",$item,-1))?"/".implode("/",explode("/",$item,-1)):"/";
            $filePath = explode("/",$item);
            $fileAuthor = "";
            $fileFullName = end($filePath);
            $fileName = $fileFullName;
            if(strstr($fileFullName,"-")) {
                $fileAuthor = strstr($fileFullName,"-",true);
                $fileName = strstr($fileFullName,"-");
            }
            $data[$type."_author"] = trim($fileAuthor);
            $data[$type."_name"] = trim($fileName,"- ");
            $data[$type."_dir"] = $fileDir;
            if("audio" == $type) {
                $fileInfoTable = new MusicFileListModel();
            }else if("video" == $type) {
                $fileInfoTable = new VideoFileListModel();
            }

            if(!$videoInfo = $fileInfoTable->where($type."_name",$data[$type."_name"])->where($type."_author",$data[$type."_author"])->find()) {
                if(!$fileInfoTable->save($data)) {
                    $msg["error"][] = $fileFullName . "添加失败";
                }else {
                    $msg["success"][] = $fileFullName . " 添加成功";
                }
            }else {
                if($videoInfo["video_status"] == -1) {
                    $msg["success"][] = $fileFullName . " 为禁用状态";
                }else if(!$videoInfo->save($data)) {
                    $msg["error"][] = $fileFullName . "更新失败";
                }else {
                    $msg["success"][] = $fileFullName . " 更新成功";
                }
            }
        }
        return returnAjax(200,$msg,true);
    }

    /**
     * 更新数据库中状态非 -1(禁用) 的文件状态 若能在本地找到则状态为 1(正常) 找不到状态为 0(找不到资源)
     * @param string $type 类型 [music/video]
     * @return \think\response\Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateFileStatusInDb(string $type = ""){
        if($type == "" && !empty($this->requestData["type"])) {
            $type = $this->requestData["type"];
        }
        $msg = [];
        if("music" == $type) {
            $fileInfoTable = new MusicFileListModel();
        }else if("video" == $type){
            $fileInfoTable = new VideoFileListModel();
        }else {
            return returnAjax(100,"类型错误",false);
        }
        $fileList = $fileInfoTable->where($type."_status","<>",-1)->select();
        $MinioFileList = json_decode((new GetDataInMinIO())->getObjectList($type)->getContent(),true)["data"];
        if(false === $MinioFileList){
            $MinioFileList = [];
        }else if(!is_array($MinioFileList)) {
            $MinioFileList = [$MinioFileList];
        }
        foreach ($fileList as $item) {
            if($item[$type."_author"] === "") {
                $fullFileName = (("/" === $item[$type."_dir"])?"" : trim($item[$type."_dir"],"/")."/").$item[$type."_name"];
            }else {
                $fullFileName = (("/" === $item[$type."_dir"])?"" : trim($item[$type."_dir"],"/")."/").$item[$type."_author"]." - ".$item[$type."_name"];
            }
            dump($MinioFileList);
            if(in_array($fullFileName,$MinioFileList)) {
                $fileInfoTable::update([$type."_status" => 1],[$type."_id" => $item[$type."_id"]]);
                $msg["find"][] =  "【".$fullFileName."】"." 可以找到，状态修改为 1 ";
            } else {
                $fileInfoTable::update([$type."_status" => 0],[$type."_id" => $item[$type."_id"]]);
                $msg["notFound"][] =  "【".$fullFileName."】"." 文件找不到，状态修改为 0 ";
            }
        }
        return returnAjax(200,$msg,true);
    }
}