<?php

use yii\db\Migration;

class m260507_030640_create_table_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('files', [
            'id'=> $this->primaryKey(),
            'file_path' => $this->string()->notNull(),
            'file_name' => $this->string()->notNull(),
            'file_type' => $this->string()->notNull(),
            'file_size' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('files');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_030640_create_table_files cannot be reverted.\n";

        return false;
    }
    */
}
