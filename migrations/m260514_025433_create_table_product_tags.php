<?php

use yii\db\Migration;

class m260514_025433_create_table_product_tags extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_tags', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-product_tags-product_id', 'product_tags', 'product_id');
        $this->createIndex('idx-product_tags-tag_id', 'product_tags', 'tag_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_tags}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260514_025433_create_table_product_tags cannot be reverted.\n";

        return false;
    }
    */
}
