<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class ScoreSource extends Migrator
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
        $table = $this->table('score_source', ['engine' => 'MyISAM', 'collation' => 'utf8_unicode_ci', 'comment' => '' ,'id' => 'source_id' ,'primary_key' => ['source_id']]);
        $table->addColumn('source_name', 'string', ['limit' => 50,'null' => false,'default' => null,'signed' => true,'comment' => '来源名称',])
			->addColumn('source_detail', 'string', ['limit' => 255,'null' => false,'default' => null,'signed' => true,'comment' => '来源详情',])
			->addColumn('score', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => false,'default' => null,'signed' => true,'comment' => '分数',])
			->addColumn('source_status', 'boolean', ['null' => false,'default' => 1,'signed' => true,'comment' => '状态 0 禁用 1 启用',])
			->addColumn('create_date', 'datetime', ['null' => false,'default' => 'CURRENT_TIMESTAMP','signed' => true,'comment' => '创建时间',])
			->addColumn('update_date', 'datetime', ['null' => false,'default' => 'CURRENT_TIMESTAMP','signed' => true,'comment' => '更新时间',])
			->addColumn('delete_date', 'datetime', ['null' => true,'signed' => true,'comment' => '删除时间',])
			->addColumn('is_delete', 'boolean', ['null' => false,'default' => 0,'signed' => true,'comment' => '是否删除',])
            ->create();
    }
}
