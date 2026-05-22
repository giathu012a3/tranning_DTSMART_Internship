<?php

use yii\db\Migration;

/**
 * Class m260522_023500_add_timestamps_to_cart_details_table
 */
class m260522_023500_add_timestamps_to_cart_details_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart_details', 'created_at', $this->integer()->notNull()->defaultValue(time()));
        $this->addColumn('cart_details', 'updated_at', $this->integer()->notNull()->defaultValue(time()));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cart_details', 'created_at');
        $this->dropColumn('cart_details', 'updated_at');
    }
}
