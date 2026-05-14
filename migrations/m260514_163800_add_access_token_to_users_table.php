<?php

use yii\db\Migration;

class m260514_163800_add_access_token_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'access_token', $this->string(255)->defaultValue(null)->after('password_hash'));

        $this->update('users', ['access_token' => 'test-token-123'], ['id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'access_token');
    }
}
