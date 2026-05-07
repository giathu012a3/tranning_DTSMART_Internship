<?php

use yii\db\Migration;

class m260506_025808_create_table_cart_details extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cart_details', [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cart_details');   
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_025808_create_table_cart_details cannot be reverted.\n";

        return false;
    }
    */
}
