<?php

use yii\db\Migration;

class m260507_071454_rename_fileables_to_assets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('fileables', 'assets');
        $this->renameColumn('assets', 'fileable_id', 'asset_id');
        $this->renameColumn('assets', 'fileable_type', 'asset_type');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->renameColumn('assets', 'asset_id', 'fileable_id');
        $this->renameColumn('assets', 'asset_type', 'fileable_type');
        $this->renameTable('assets', 'fileables');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_071454_rename_fileables_to_assets cannot be reverted.\n";

        return false;
    }
    */
}
