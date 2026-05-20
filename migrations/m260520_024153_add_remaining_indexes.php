<?php

use yii\db\Migration;

class m260520_024153_add_remaining_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-assets-asset_id-asset_type',
            'assets',
            ['asset_id', 'asset_type']
        );
        $this->createIndex(
            'idx-assets-file_id',
            'assets',
            'file_id'
        );

        $this->createIndex(
            'idx-article_tags-article_id',
            'article_tags',
            'article_id'
        );
        $this->createIndex(
            'idx-article_tags-tag_id',
            'article_tags',
            'tag_id'
        );

        $this->createIndex(
            'idx-product_articles-product_id',
            'product_articles',
            'product_id'
        );
        $this->createIndex(
            'idx-product_articles-article_id',
            'product_articles',
            'article_id'
        );

        $this->createIndex(
            'idx-order_details-order_id',
            'order_details',
            'order_id'
        );
        $this->createIndex(
            'idx-order_details-product_id',
            'order_details',
            'product_id'
        );

        $this->createIndex(
            'idx-articles-deleted_at',
            'articles',
            'deleted_at'
        );
        $this->createIndex(
            'idx-articles-status',
            'articles',
            'status'
        );
        $this->createIndex(
            'idx-articles-author_id',
            'articles',
            'author_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-assets-asset_id-asset_type', 'assets');
        $this->dropIndex('idx-assets-file_id', 'assets');

        $this->dropIndex('idx-article_tags-article_id', 'article_tags');
        $this->dropIndex('idx-article_tags-tag_id', 'article_tags');

        $this->dropIndex('idx-product_articles-product_id', 'product_articles');
        $this->dropIndex('idx-product_articles-article_id', 'product_articles');

        $this->dropIndex('idx-order_details-order_id', 'order_details');
        $this->dropIndex('idx-order_details-product_id', 'order_details');

        $this->dropIndex('idx-articles-deleted_at', 'articles');
        $this->dropIndex('idx-articles-status', 'articles');
        $this->dropIndex('idx-articles-author_id', 'articles');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260520_024153_add_remaining_indexes cannot be reverted.\n";

        return false;
    }
    */
}
