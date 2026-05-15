<?php

use yii\db\Migration;


class m260514_040200_add_deleted_at_column_to_articles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('articles', 'deleted_at', $this->integer()->null()->defaultValue(null)->after('updated_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('articles', 'deleted_at');
    }
}
