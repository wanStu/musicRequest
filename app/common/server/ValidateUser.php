<?php


namespace app\common\server;


use app\common\Base;
use app\common\model\ThinkAuthRuleModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\wenhainan\Auth;

class ValidateUser extends Base
{

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
                return returnAjax(200,"$ruleName 权限验证通过",true);
            } else {
                return returnAjax(100,"用户 {$uid} 无 $ruleName 权限",false);
            }
        } else {
            return returnAjax(100,"用户 {$uid} 无 $ruleName 权限",false);
        }
    }
}