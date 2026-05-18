<?php

use yii\db\Migration;

/**
 * Class m260516_050600_add_index_to_categories_status
 */
class m260516_050600_add_index_to_categories_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-categories-status',
            '{{%categories}}',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-categories-status',
            '{{%categories}}'
        );
    }
}
