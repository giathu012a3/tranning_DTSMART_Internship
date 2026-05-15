<?php

use yii\db\Migration;

class m260515_033902_update_table_coupon_usages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coupon_usages','updated_at',$this->integer()->notNull()->after('created_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260515_033902_update_table_coupon_usages cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260515_033902_update_table_coupon_usages cannot be reverted.\n";

        return false;
    }
    */
}
