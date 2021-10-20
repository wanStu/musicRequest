<?php

namespace app\common\service;
use Aws\S3\S3Client;
class GetDataInMinIO
{
    protected $s3;
    public function __construct() {
        $minioHost = config("app.MinIO_Host");
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => 'cn-north-1',
            'endpoint'  =>  $minioHost,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => 'minioadmin',
                'secret' => 'minioadmin',
            ]
        ]);
    }
    protected function getBucketsList() {
        $bucketsList = [];
        if(!empty($this->s3->ListBuckets()["Buckets"])) {
            $listBuckets = $this->s3->ListBuckets()["Buckets"];
            $bucketsList = [];
            foreach ($listBuckets as $value) {
                $bucketsList = array_merge_recursive($value,$bucketsList);
            }
            unset($value);
             $bucketsList = $bucketsList["Name"];
        }
        return returnAjax(200,"获取成功",$bucketsList);
    }

    public function getObjectList($type = "video") {
        $bucketsList = json_decode($this->getBucketsList()->getContent(),true)["data"];
        if(!in_array($type,$bucketsList)) {
            return returnAjax(200,"获取失败，类型错误",false);
        }
        $listObject = $this->s3->listObjects(['Bucket' => $type]);
        $fileList = [];
        if($listObject["Contents"]) {
            foreach ($listObject["Contents"] as $value) {
                $fileList = array_merge_recursive($value,$fileList);
            }
            unset($value);
            return returnAjax(200,"获取成功",$fileList["Key"]);
        }
        return returnAjax(200,"获取成功",[]);
    }
    public function getObject($path = "/",$type = "video") {
        $objectUrl = $this->s3->getObjectUrl($type,$path);
        return $objectUrl;
    }
}