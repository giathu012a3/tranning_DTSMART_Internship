<?php

use yii\db\Migration;

class m260507_030346_create_table_article_comments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('article_comments', [
            'id' => $this->primaryKey(),
            'article_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'parent_id' => $this->integer()->defaultValue(null),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // creates index for column `article_id`
        $this->createIndex(
            'idx-article_comments-article_id',
            'article_comments',
            'article_id'
        );

        // add foreign key for table `articles`
        $this->addForeignKey(
            'fk-article_comments-article_id',
            'article_comments',
            'article_id',
            'articles',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('article_comments');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_030346_create_table_article_comments cannot be reverted.\n";

        return false;
    }
    */
}
