<?php

use yii\db\Migration;

class m260507_025704_create_table_product_articles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_articles', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'article_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('product_articles');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_025704_create_table_product_articles cannot be reverted.\n";

        return false;
    }
    */
}
