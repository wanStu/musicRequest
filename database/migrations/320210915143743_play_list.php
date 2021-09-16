<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class PlayList extends Migrator
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
        $table = $this->table('play_list', ['engine' => 'MyISAM', 'collation' => 'utf8_unicode_ci', 'comment' => '' ,'id' => 'list_id' ,'primary_key' => ['list_id']]);
        $table->addColumn('file_name', 'string', ['limit' => 100,'null' => false,'default' => null,'signed' => true,'comment' => '',])
			->addColumn('file_path', 'string', ['limit' => 100,'null' => false,'default' => null,'signed' => true,'comment' => '',])
			->addColumn('create_time', 'datetime', ['null' => false,'default' => 'CURRENT_TIMESTAMP','signed' => true,'comment' => '',])
			->addColumn('delete_time', 'datetime', ['null' => true,'signed' => true,'comment' => '',])
			->addColumn('update_time', 'datetime', ['null' => true,'signed' => true,'comment' => '',])
			->addColumn('is_delete', 'boolean', ['null' => false,'default' => 0,'signed' => true,'comment' => '',])
			->addColumn('uid', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => false,'default' => null,'signed' => true,'comment' => '',])
            ->create();
    }
}
