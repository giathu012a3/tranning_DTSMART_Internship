<?php

use yii\db\Migration;

/**
 * Class m260518_040500_add_deleted_at_to_categories_table
 */
class m260518_040500_add_deleted_at_to_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('categories', 'deleted_at', $this->integer()->null());
        $this->createIndex('idx-categories-deleted_at', 'categories', 'deleted_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-categories-deleted_at', 'categories');
        $this->dropColumn('categories', 'deleted_at');
    }
}
