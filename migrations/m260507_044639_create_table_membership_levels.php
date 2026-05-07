<?php

use yii\db\Migration;

class m260507_044639_create_table_membership_levels extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('membership_levels', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'points_required' => $this->integer()->notNull()->defaultValue(0),
            'discount_rate' => $this->decimal(5, 2)->notNull()->defaultValue(0.00),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('membership_levels');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_044639_create_table_membership_levels cannot be reverted.\n";

        return false;
    }
    */
}
