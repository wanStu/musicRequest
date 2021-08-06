<?php


namespace app\server;


use app\model\ThinkAuthRuleModel;
use app\model\VideoFileListModel;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\wenhainan\Auth;

/**
 * 从数据库中获取数据
 * Class getDataInDbServer
 * @package app\server
 */
class GetDataInDbServer
{
    /**
     * 获取数据库中的音乐/视频列表
     * @param string $type 类型 [music/video]
     * @return array|string 以Array格式返回数据库中的音乐/视频列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getFileListInDb(string $type) {
        if("music" == $type) {
            $db = new ThinkAuthRuleModel();
        }else if($type == "video") {
            $db = new VideoFileListModel();
        }else {
            return "类型错误";
        }
        return $db->where($type."_status",1)->select()->toArray();
    }

    /**
     * 验证用户是否具有某项规则的权限
     * @param string $ruleName 规则名
     * @param int $uid 用户ID
     * @var array $ruleNameList 从数据库获取到所有规则名
     * @var boolean $flag true 规则存在|false 不存在
     * @return bool|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function validateUserPermission(string $ruleName, int $uid) {
        $ruleNameList = ThinkAuthRuleModel::field("name")->select();
        $ruleNameList = json_decode($ruleNameList,true);
        $tempRuleList = [];
        foreach ($ruleNameList as $item) {
            $tempRuleList = array_merge_recursive($tempRuleList,$item);
        }
        $ruleNameList = $tempRuleList["name"];
        $ruleExist = in_array($ruleName,$ruleNameList);
        if($ruleExist) {
            if ((Auth::instance())->check($ruleName, $uid)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }
}