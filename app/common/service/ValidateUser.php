<?php


namespace app\common\service;


use app\common\controller\Base;
use app\common\model\AuthRuleModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\wenhainan\Auth;

class ValidateUser extends Base
{
    /**
     * 验证用户是否具有某项规则的权限
     * @param string $ruleName 规则名
     * @param int $user_id 用户ID
     * @var array $ruleNameList 从数据库获取到所有规则名
     * @var boolean $flag true 规则存在|false 不存在
     * @return bool|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function validateUserPermission(string $ruleName, int $user_id) {
        $ruleNameList = AuthRuleModel::field("name")->select();
        $ruleNameList = json_decode($ruleNameList,true);
        $tempRuleList = [];
        foreach ($ruleNameList as $item) {
            $tempRuleList = array_merge_recursive($tempRuleList,$item);
        }
        $ruleNameList = $tempRuleList["name"];
        $ruleExist = in_array($ruleName,$ruleNameList);
        if($ruleExist) {
            if ((Auth::instance())->check($ruleName, $user_id)) {
                return returnAjax(200,"$ruleName 权限验证通过",true);
            } else {
                return returnAjax(100,"用户 {$user_id} 无 $ruleName 权限",false);
            }
        } else {
            return returnAjax(100,"用户 {$user_id} 无 $ruleName 权限",false);
        }
    }
}