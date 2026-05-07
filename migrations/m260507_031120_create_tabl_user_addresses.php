<?php

use yii\db\Migration;

class m260507_031120_create_tabl_user_addresses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_addresses', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'full_name' => $this->string()->notNull(),
            'phone' => $this->string()->notNull(),
            'address' => $this->text()->notNull(),
            'is_default' => $this->integer()->notNull()->defaultValue(0),
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
        $this->dropTable('user_addresses');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_031120_create_tabl_user_addresses cannot be reverted.\n";

        return false;
    }
    */
}
