<?php

use yii\db\Migration;

class m260507_032529_create_table_role_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('role_permissions', [
            'id' => $this->primaryKey(),
            'role_id' => $this->integer()->notNull(),
            'permission_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('role_permissions');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_032529_create_table_role_permissions cannot be reverted.\n";

        return false;
    }
    */
}
