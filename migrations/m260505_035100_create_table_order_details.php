<?php

use yii\db\Migration;

class m260505_035100_create_table_order_details extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_details', [
            'id' => $this->primaryKey(),
            'order_id'=>$this->integer()->notNull(),
            'product_id'=>$this->integer()->notNull(),
            'quantity'=>$this->integer()->notNull(),
            'price'=>$this->decimal(10, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_details');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_035100_create_table_order_details cannot be reverted.\n";

        return false;
    }
    */
}
