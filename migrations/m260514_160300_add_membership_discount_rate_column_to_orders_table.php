<?php

use yii\db\Migration;

class m260514_160300_add_membership_discount_rate_column_to_orders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('orders', 'membership_discount_rate', $this->decimal(5, 2)->defaultValue(0.00)->after('membership_level_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('orders', 'membership_discount_rate');
    }
}
