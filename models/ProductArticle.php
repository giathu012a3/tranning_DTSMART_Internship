<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_articles".
 *
 * @property int $id
 * @property int $product_id
 * @property int $article_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Article $article
 */
class ProductArticle extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_articles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'article_id', 'created_at', 'updated_at'], 'required'],
            [['product_id', 'article_id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'article_id' => 'Article ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Article]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }
}
