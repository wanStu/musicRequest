<?php

use app\common\middleware\LoginCheck;
use think\facade\Route;


Route::rule("index","/","POST|GET");
//用户登录
Route::rule("userLogin","User/userLogin","POST|GET");
Route::rule("createUser","User/createUser","POST|GET");
//获取信息 无需登录
Route::group("getInfo",function () {
    //getInfo
    //获取播放列表
    Route::rule("getPlaylist","GetInfoNoLogin/getPlaylist","POST|GET");
    //获取文件列表
    Route::rule("getFileList","GetInfoNoLogin/getFileList","POST|GET");
    //获取用户组列表
    Route::rule("getUserGroupList","GetInfoNoLogin/getUserGroupList","POST|GET");
    //获取积分来源列表
    Route::rule("getScoreSourceList","GetInfoNoLogin/getScoreSourceList","POST|GET");
});


//获取信息 需要登录
Route::group("getInfo",function () {
    //getInfo
    //获取用户信息
    Route::rule("getUserInfo","GetInfo/getUserInfo","POST|GET");
    //获取用户分数详情
    Route::rule("getUserScoreInfo","GetInfo/getUserScoreInfo","POST|GET");
    Route::rule("getObjectList","GetInfo/getObjectList","POST|GET");
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
    //添加权限
    Route::rule("addPermission","UseFunction/addPermission","POST|GET");
    //禁用权限
    Route::rule("deletePermission","UseFunction/deletePermission","POST|GET");

    //删除 MinIO中的文件
    Route::rule("deleteObject","UseFunction/deleteObject","POST|GET");
    //向 MinIO 中上传文件
    Route::rule("updateObject","UseFunction/updateObject","POST|GET");
    //创建用户组
    Route::rule("createUserGroup","UseFunction/createUserGroup","POST|GET");
    //禁用用户组
    Route::rule("deleteUserGroup","UseFunction/deleteUserGroup","POST|GET");

    //添加 积分来源
    Route::rule("addScoreSource","UseFunction/addScoreSource","POST|GET");
    //删除 积分来源
    Route::rule("deleteScoreSource","UseFunction/deleteScoreSource","POST|GET");
    //测试
    Route::rule("Test","UseFunction/Test","POST|GET");
})->middleware(LoginCheck::class);