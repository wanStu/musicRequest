<?php

namespace app\common\service;
use Aws\S3\S3Client;
class GetDataInMinIO
{
    protected $s3;
    public function __construct() {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => 'cn-north-1',
            'endpoint'  =>  config("app.MinIO_Host"),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => config("app.MinIO_User"),
                'secret' => config("app.MinIO_Pwd"),
            ]
        ]);
    }

    /**
     * 获取桶列表
     * @return \type
     */
    public function getBucketsList() {
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
        if(!is_array($bucketsList)) {
            $bucketsList = [$bucketsList];
        }
        return returnAjax(200,"获取成功",$bucketsList);
    }

    /**
     * 获取 对象 列表
     * @param string $Bucket 要获取的桶名 默认 "video"
     * @return \type
     */
    public function getObjectList($Bucket = "video") {
        $bucketsList = json_decode($this->getBucketsList()->getContent(),true)["data"];
        if(!in_array($Bucket,$bucketsList)) {
            return returnAjax(200,"获取失败，类型错误",false);
        }
        $listObject = $this->s3->listObjects(['Bucket' => $Bucket]);
        $fileList = [];
        if($listObject["Contents"]) {
            foreach ($listObject["Contents"] as $value) {
                $fileList = array_merge_recursive($value,$fileList);
            }
            unset($value);
            if(!is_array($fileList)) {
                $fileList = [$fileList];
            }
            return returnAjax(200,"获取成功",$fileList["Key"]);
        }else {
            return returnAjax(200,"获取成功",[]);
        }
    }

    /**
     * 获取 对象
     * @param string $path   路径 默认 "/"
     * @param string $Bucket 要获取的桶名 默认 "video"
     * @return string
     */
    public function getObject($path = "/",$Bucket = "video") {
        $objectUrl = $this->s3->getObjectUrl($Bucket,$path);
        return $objectUrl;
    }
}