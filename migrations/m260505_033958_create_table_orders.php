<?php

use yii\db\Migration;

class m260505_033958_create_table_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('orders', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'full_name' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'phone' => $this->string()->notNull(),
            'address' => $this->string()->notNull(),
            'membership_level_id' => $this->integer()->null(),
            'discount_amount' => $this->decimal(10, 2)->notNull(),
            'total' => $this->decimal(10, 2)->notNull(),
            'final_total' => $this->decimal(10, 2)->notNull(),
            'payment_method' => $this->string()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('orders');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_033958_create_table_orders cannot be reverted.\n";

        return false;
    }
    */
}
