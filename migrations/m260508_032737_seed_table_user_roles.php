<?php

use yii\db\Migration;

class m260508_032737_seed_table_user_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('user_roles', ['user_id', 'role_id', 'created_at', 'updated_at'], [
            [6, 4, time(), time()],
            [7, 5, time(), time()],
            [8, 6, time(), time()],
            [9, 6, time(), time()],
            [10, 6, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('user_roles', ['IN', 'id', [6, 7, 8, 9, 10]]);

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260508_032737_seed_table_user_roles cannot be reverted.\n";

        return false;
    }
    */
}
