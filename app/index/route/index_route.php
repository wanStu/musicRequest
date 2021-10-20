<?php

use app\common\middleware\LoginCheck;
use think\facade\Route;


Route::rule("index","/","POST|GET");
Route::rule("userLogin","UserLogin/index","POST|GET");
//获取信息 无需登录
Route::group("getInfo",function () {
    //getInfo
    //获取文件列表
    Route::rule("getFileList","GetInfo/getFileList","POST|GET");
    Route::rule("getObjectList","GetInfo/getObjectList","POST|GET");
    //获取播放列表
    Route::rule("getPlaylist","GetInfo/getPlaylist","POST|GET");
});


//获取信息 需要登录
Route::group("getInfo",function () {
    //getInfo
    //获取用户信息
    Route::rule("getUserInfo","GetInfo/getUserInfo","POST|GET");
    //获取用户分数详情
    Route::rule("getUserScoreInfo","GetInfo/getUserScoreInfo","POST|GET");
})->middleware(LoginCheck::class);

//操作 需要登陆
Route::group("useFunction",function (){
    //updateInfo
    //更新数据库中文件列表
    Route::rule("updateFileDataToDb","UpdateInfo/updateFileListDataToDb","POST|GET");
    //更新数据库中文件状态
    Route::rule("updateFileStatusInDb","UpdateInfo/updateFileStatusInDb","POST|GET");


    //useFunction
    //验证用户权限
    Route::rule("validateUserPermission","UseFunction/validateUserPermission","POST|GET");
    //开始直播
    Route::rule("liveStart","UseFunction/liveStart","POST|GET");
    //将视频加入播放
    Route::rule("addVideoToPlaylist","UseFunction/addVideoToPlaylist","POST|GET");
    //发布当播放列表为空时自动添加一个视频的任务
    Route::rule("RandomAddVideoToPlaylist","UseFunction/RandomAddVideoToPlaylist","POST|GET");
    //编辑用户组权限
    Route::rule("editPermissionToGroup","UseFunction/editPermissionToGroup","POST|GET");
    //获取用户的权限列表，同时返回用户组id列表
    Route::rule("getPermissionListOnUser","UseFunction/getPermissionListOnUser","POST|GET");
    //单独获取 用户的用户组列表
    Route::rule("getGroupInfoOnUser","UseFunction/getGroupInfoOnUser","POST|GET");
    //单独获取 用户组权限
    Route::rule("getPermissionListOnGroup","UseFunction/getPermissionListOnGroup","POST|GET");
    //单独获取 权限列表
    Route::rule("getPermissionList","UseFunction/getPermissionList","POST|GET");

    //删除 MinIO中的文件
    Route::rule("deleteObject","UseFunction/deleteObject","POST|GET");
    //向 MinIO 中上传文件
    Route::rule("updateObject","UseFunction/updateObject","POST|GET");
    //测试
    Route::rule("getObjectTest","UseFunction/getObjectTest","POST|GET");
})->middleware(LoginCheck::class);