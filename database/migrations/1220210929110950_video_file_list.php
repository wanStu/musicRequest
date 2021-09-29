<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class VideoFileList extends Migrator
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
        $table = $this->table('video_file_list', ['engine' => 'MyISAM', 'collation' => 'utf8_general_ci', 'comment' => '歌曲文件列表' ,'id' => 'video_id' ,'primary_key' => ['video_id']]);
        $table->addColumn('video_author', 'string', ['limit' => 100,'null' => false,'default' => null,'signed' => true,'comment' => '视频作者',])
			->addColumn('video_name', 'string', ['limit' => 255,'null' => false,'default' => null,'signed' => true,'comment' => '视频名',])
			->addColumn('video_dir', 'string', ['limit' => 100,'null' => false,'default' => null,'signed' => true,'comment' => '视频位置',])
			->addColumn('video_status', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => false,'default' => 1,'signed' => true,'comment' => '视频状态 -1(禁用)|0(找不到资源)|1(正常)',])
            ->create();
    }
}
