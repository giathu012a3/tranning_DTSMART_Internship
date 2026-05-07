<?php

use yii\db\Migration;

class m260507_030913_create_tabl_fileables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('fileables', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'fileable_id' => $this->integer()->notNull(),
            'fileable_type' => $this->string()->notNull(),
            'collection_name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('fileables');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_030913_create_tabl_fileables cannot be reverted.\n";

        return false;
    }
    */
}
