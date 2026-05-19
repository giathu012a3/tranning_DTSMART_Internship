<?php

use yii\db\Migration;

class m260519_163000_alter_price_columns_to_decimal_15_2 extends Migration
{
    public function safeUp()
    {
        // Alter products table
        $this->alterColumn('products', 'price', $this->decimal(15, 2)->notNull());

        // Alter orders table
        $this->alterColumn('orders', 'total', $this->decimal(15, 2)->notNull());
        $this->alterColumn('orders', 'discount_amount', $this->decimal(15, 2)->notNull());
        $this->alterColumn('orders', 'final_total', $this->decimal(15, 2)->notNull());

        // Alter order_details table
        $this->alterColumn('order_details', 'price', $this->decimal(15, 2)->notNull());

        // Alter coupons table
        $this->alterColumn('coupons', 'value', $this->decimal(15, 2)->notNull());
        $this->alterColumn('coupons', 'max_amount', $this->decimal(15, 2)->null());
        $this->alterColumn('coupons', 'min_purchase', $this->decimal(15, 2)->null());

        // Alter coupon_usages table
        $this->alterColumn('coupon_usages', 'applied_value', $this->decimal(15, 2)->notNull());
        $this->alterColumn('coupon_usages', 'applied_max_amount', $this->decimal(15, 2)->null());
    }

    public function safeDown()
    {
        // Revert to decimal(10, 2)
        $this->alterColumn('products', 'price', $this->decimal(10, 2)->notNull());

        $this->alterColumn('orders', 'total', $this->decimal(10, 2)->notNull());
        $this->alterColumn('orders', 'discount_amount', $this->decimal(10, 2)->notNull());
        $this->alterColumn('orders', 'final_total', $this->decimal(10, 2)->notNull());

        $this->alterColumn('order_details', 'price', $this->decimal(10, 2)->notNull());

        $this->alterColumn('coupons', 'value', $this->decimal(10, 2)->notNull());
        $this->alterColumn('coupons', 'max_amount', $this->decimal(10, 2)->null());
        $this->alterColumn('coupons', 'min_purchase', $this->decimal(10, 2)->null());

        $this->alterColumn('coupon_usages', 'applied_value', $this->decimal(10, 2)->notNull());
        $this->alterColumn('coupon_usages', 'applied_max_amount', $this->decimal(10, 2)->null());
    }
}
