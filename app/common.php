<?php
// 应用公共文件
/**
 * 自定义ajax返回函数
 * @param int $status 返回状态值
 * @param string $msg 返回提示信息
 * @param type $data 返回数据内容
 * @param string $type 返回数据类型
 * @return type
 */
function returnAjax($status, $msg, $data = '', $type = 'json')
{
    $rData = array(
        'status' => $status,
        'msg' => $msg,
        'data' => $data,
        'type' => $type
    );
    //返回数据类型
    switch ($type) {
        case 'json':
            return json($rData);
        case 'xml':
            break;
        default :
            break;
    }
}