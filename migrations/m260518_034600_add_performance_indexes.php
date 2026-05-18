<?php

use yii\db\Migration;

/**
 * Class m260518_034600_add_performance_indexes
 */
class m260518_034600_add_performance_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Indexes for products table
        $this->createIndex(
            'idx-products-category_id',
            'products',
            'category_id'
        );
        $this->createIndex(
            'idx-products-status',
            'products',
            'status'
        );
        $this->createIndex(
            'idx-products-deleted_at',
            'products',
            'deleted_at'
        );

        $this->createIndex(
            'idx-orders-user_id-deleted_at',
            'orders',
            ['user_id', 'deleted_at']
        );
        
        $this->createIndex(
            'idx-orders-status',
            'orders',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-products-category_id', 'products');
        $this->dropIndex('idx-products-status', 'products');
        $this->dropIndex('idx-products-deleted_at', 'products');

        $this->dropIndex('idx-orders-user_id-deleted_at', 'orders');
        $this->dropIndex('idx-orders-status', 'orders');
    }
}
