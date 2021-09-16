<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class MusicFileList extends Migrator
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
        $table = $this->table('music_file_list', ['engine' => 'MyISAM', 'collation' => 'utf8_general_ci', 'comment' => '歌曲文件列表' ,'id' => 'music_id' ,'primary_key' => ['music_id']]);
        $table->addColumn('music_author', 'string', ['limit' => 50,'null' => false,'default' => null,'signed' => true,'comment' => '歌曲作者',])
			->addColumn('music_name', 'string', ['limit' => 50,'null' => false,'default' => null,'signed' => true,'comment' => '歌曲名',])
			->addColumn('music_dir', 'string', ['limit' => 100,'null' => false,'default' => null,'signed' => true,'comment' => '歌曲位置',])
			->addColumn('music_status', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => false,'default' => 1,'signed' => true,'comment' => '歌曲状态 -1(禁用)|0(找不到资源)|1(正常)',])
            ->create();
    }
}
