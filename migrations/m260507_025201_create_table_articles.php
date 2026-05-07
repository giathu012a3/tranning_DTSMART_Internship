<?php

use yii\db\Migration;

class m260507_025201_create_table_articles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('articles', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'slug'=>$this->string()->notNull()->unique(),
            'excerpt'=>$this->string()->notNull(),
            'like_count'=>$this->integer()->defaultValue(0),
            'author_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('articles');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_025201_create_table_articles cannot be reverted.\n";

        return false;
    }
    */
}
