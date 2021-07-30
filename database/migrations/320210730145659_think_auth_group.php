<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class ThinkAuthGroup extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('think_auth_group', ['engine' => 'MyISAM', 'collation' => 'utf8_general_ci', 'comment' => '用户组表' ,'id' => 'id','signed' => true ,'primary_key' => ['id']]);
        $table->addColumn('title', 'char', ['limit' => 100,'null' => false,'default' => '','signed' => true,'comment' => '用户组中文名称',])
			->addColumn('status', 'boolean', ['null' => false,'default' => 1,'signed' => true,'comment' => '状态：为1正常，为0禁用',])
			->addColumn('rules', 'char', ['limit' => 80,'null' => false,'default' => '','signed' => true,'comment' => '用户组拥有的规则id， 多个规则","隔开，',])
            ->create();
    }
}
