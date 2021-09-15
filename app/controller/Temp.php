<?php


namespace app\controller;


use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

class Temp
{
    public function index() {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe",
            'timeout'          => 360
        ]);
        $video = $ffmpeg->open(root_path() . "public/static/videoFile/周杰伦 - 七里香.mp4");
        $format = new X264();
        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $format->on('progress', function ($audio, $format, $percentage) {
            static $percentageCopy = 0;
            if($percentage != $percentageCopy) {
                $percentageCopy = $percentage;
                echo "进度 {$percentage} % ",PHP_EOL;
            }
        });
        $video->save($format, "/temp");
    }
    public function Temp() {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => root_path() . "public/static/ffmpeg/ffmpeg.exe",
            'ffprobe.binaries' => root_path() . "public/static/ffmpeg/ffprobe.exe",
            'timeout'          => 360
        ]);
        $video = $ffmpeg->open(root_path() . "public/static/videoFile/周杰伦 - 七里香.mp4");
        $format = new X264();
        $format
            ->setInitialParameters(["-re","-i"])
            ->setAudioKiloBitrate(192)
            ->setAdditionalParameters(["-f","flv"]);
        $format->on('progress', function ($audio, $format, $percentage) {
            static $percentageCopy = 0;
            if($percentage != $percentageCopy) {
                $percentageCopy = $percentage;
                echo "进度 {$percentage} % ",PHP_EOL;
            }
        });
        $video->save($format, "/temp");
    }
}