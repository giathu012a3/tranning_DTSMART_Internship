<?php

use yii\db\Migration;

class m260505_095410_create_table_coupon_usages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('coupon_usages', [
            'id' => $this->primaryKey(),
            'coupon_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'applied_code' => $this->string()->notNull(),
            'applied_type' => $this->string()->notNull(),
            'applied_value' => $this->decimal(10, 2)->notNull(),
            'applied_max_amount' => $this->decimal(10, 2)->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('coupon_usages');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_095410_create_table_coupon_usages cannot be reverted.\n";

        return false;
    }
    */
}
