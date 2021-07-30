<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class ThinkAuthGroupAccess extends Migrator
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
        $table = $this->table('think_auth_group_access', ['engine' => 'MyISAM', 'collation' => 'utf8_general_ci', 'comment' => '用户组明细表' ,'id' => false ]);
        $table->addColumn('uid', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM,'null' => false,'default' => null,'signed' => false,'comment' => '用户id',])
			->addColumn('group_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM,'null' => false,'default' => null,'signed' => false,'comment' => '用户组id',])
			->addIndex(['group_id'], ['name' => 'group_id'])
			->addIndex(['uid','group_id'], ['unique' => true,'name' => 'uid_group_id'])
			->addIndex(['uid'], ['name' => 'uid'])
            ->create();
    }
}
