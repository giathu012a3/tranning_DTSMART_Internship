<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

class ProductArticleModel extends ProductArticle
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['product_id', 'article_id'], 'required'],
            [['product_id', 'article_id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ProductModel::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(ArticleModel::class, ['id' => 'article_id']);
    }
}
