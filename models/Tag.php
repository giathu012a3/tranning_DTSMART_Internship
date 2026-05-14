<?php

namespace app\models;

use Override;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ArticleTag[] $articleTags
 * @property Article[] $articles
 */
class Tag extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1],
            [['name', 'slug'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => \yii\behaviors\SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleTags()
    {
        return $this->hasMany(ArticleTag::class, ['tag_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])->via('articleTags')->active();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTags()
    {
        return $this->hasMany(ProductTag::class, ['tag_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productTags')->active();
    }
}
