<?php

namespace app\common\service;

use Aws\finspace\finspaceClient;
use Aws\S3\S3Client;

class UpdateDataToMinIO
{
    protected $s3;
    public function __construct() {
        $this->s3 = new S3Client([
            'version'                   => 'latest',
            'region'                    => 'cn-north-1',
            'endpoint'                  =>  config("app.MinIO_Host"),
            'use_path_style_endpoint'   => true,
            'credentials'               => [
                'key'    => config("app.MinIO_User"),
                'secret' => config("app.MinIO_Pwd"),
            ]
        ]);
        bind("GetDataInMinIO",GetDataInMinIO::class);
        bind("UpdateFileInfoToDbServer",UpdateFileInfoToDbServer::class);
    }

    /**
     * 上传对象
     * @param String $bucket 要被上传的桶名
     * @param Object $Body   要上传的文件
     * @param String $Key    要被上传的路径 默认 ”/“
     * @return \type
     */
    public function updateObject($Body,$fileFullName,$bucket="video",$Key = "/") {
        $bucketsList = json_decode(app("GetDataInMinIO")->getBucketsList()->getContent(),true)["data"];
        if(!in_array($bucket,$bucketsList)) {
            $createBucketResult = $this->s3->createBucket([
                "ACL"    => "public-read-write",
                "Bucket" => $bucket,
            ]);
            if(!$createBucketResult) {
                return returnAjax(100,"创建桶 {$bucket} 失败",false);
            }
        }
        $objectList = json_decode(app("GetDataInMinIO")->getObjectList($bucket)->getContent(),true)["data"];
        if(in_array(trim($Key.$fileFullName,"/"),$objectList)) {
            return returnAjax(100,$fileFullName." 已存在",false);
        }
        $putObjectResult = $this->s3->putObject([
            "ACL"    =>  "public-read-write",
            "Bucket" =>  $bucket,
            "Key"    =>  $Key.$fileFullName,
            "Body"   =>  $Body,
        ]);
        if ($putObjectResult) {
            app("UpdateFileInfoToDbServer")->updateFileListToDb($bucket);
            app("UpdateFileInfoToDbServer")->updateFileStatusInDb($bucket);
            return returnAjax(200,$fileFullName." 上传成功",true);
        }else {
            return returnAjax(100,$fileFullName." 上传失败",false);
        }
    }

    /**
     * @param String $bucket 要删除的文件桶名
     * @param String $Path 要删除的文件路径 包含文件名
     */
    public function deleteObject($bucket,$path) {
        $bucketsList = json_decode(app("GetDataInMinIO")->getBucketsList()->getContent(),true)["data"];
        if(!in_array($bucket,$bucketsList)) {
            return returnAjax(100,"桶 $bucket 不存在",false);
        }
        $objectList = json_decode(app("GetDataInMinIO")->getObjectList($bucket)->getContent(),true)["data"];
        if(!in_array(trim($path,"/"),$objectList)) {
            return returnAjax(100,"文件 $path 不存在",false);
        }
        $deleteObjectResult = $this->s3->deleteObject([
            "Bucket"    => $bucket,
            "Key"       => $path,
        ]);
        if($deleteObjectResult) {
            app("UpdateFileInfoToDbServer")->updateFileListToDb($bucket);
            app("UpdateFileInfoToDbServer")->updateFileStatusInDb($bucket);
            return returnAjax(200,"删除成功",true);
        }else {
            return returnAjax(100,"删除失败",false);
        }
    }
}