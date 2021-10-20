<?php

namespace app\common\job;

use think\queue\Job;

class UploadToMinio
{
    public function fire(Job $job,$data) {
        $this->uploadFile($data);
    }
    protected function uploadFile($data) {
        if(is_array($data["file"])) {
            foreach ($data["file"] as $value) {
                $updateObjectResult[] = json_decode(app("UpdateDataToMinIO")->updateObject(fopen($value,"r"),$value->getOriginalName())->getContent(),true)["msg"];
            }
            unset($value);
        }else {
            $updateObjectResult = json_decode(app("UpdateDataToMinIO")->updateObject($data["file"],$data["file"]->getOriginalName())->getContent(),true)["msg"];
        }
        dump($updateObjectResult);
    }
}