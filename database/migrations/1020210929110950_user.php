<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class User extends Migrator
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
        $table = $this->table('user', ['engine' => 'MyISAM', 'collation' => 'utf8_unicode_ci', 'comment' => '' ,'id' => 'user_id' ,'primary_key' => ['user_id']]);
        $table->addColumn('user_name', 'string', ['limit' => 20,'null' => false,'default' => null,'signed' => true,'comment' => '',])
			->addColumn('user_pwd', 'string', ['limit' => 255,'null' => false,'default' => null,'signed' => true,'comment' => '',])
			->addColumn('create_date', 'datetime', ['null' => false,'default' => 'CURRENT_TIMESTAMP','signed' => true,'comment' => '',])
			->addColumn('update_date', 'datetime', ['null' => true,'signed' => true,'comment' => '',])
			->addColumn('delete_date', 'datetime', ['null' => true,'signed' => true,'comment' => '',])
			->addColumn('is_delete', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => false,'default' => 0,'signed' => true,'comment' => '',])
			->addIndex(['user_name'], ['unique' => true,'name' => 'user_user_name_uindex'])
            ->create();
    }
}
