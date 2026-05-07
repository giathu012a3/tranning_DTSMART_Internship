<?php

use yii\db\Migration;

class m260505_035925_create_table_coupons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('coupons', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'type' => $this->string()->notNull(),
            'value' => $this->decimal(10, 2)->notNull(),
            'max_amount' => $this->decimal(10, 2)->null(),
            'min_purchase' => $this->decimal(10, 2)->null(),
            'usage_limit' => $this->integer()->null(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'start_date' => $this->integer()->notNull(),
            'expiry_date' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('coupons');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_035925_create_table_coupons cannot be reverted.\n";

        return false;
    }
    */
}
