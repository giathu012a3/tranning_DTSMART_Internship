<?php

namespace app\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use app\behaviors\UploadAssetBehavior;

class ArticleWithRelations extends Article
{
    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $comment_count;

    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
            TimestampBehavior::class,
            [
                'class' => UploadAssetBehavior::class,
                'attributes' => [
                    'thumbnail' => 'articles',
                    'images'    => 'article_gallery',
                ],
            ],
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['deleted_image_ids', 'thumbnail', 'images'], 'safe'],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleComments()
    {
        return $this->hasMany(ArticleComment::class, ['article_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleLikes()
    {
        return $this->hasMany(ArticleLike::class, ['article_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleTags()
    {
        return $this->hasMany(ArticleTag::class, ['article_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('articleTags');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductArticles()
    {
        return $this->hasMany(ProductArticle::class, ['article_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productArticles')->notDeleted();
    }

    public function getActiveProducts()
    {
        return $this->getProducts()->active();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(Asset::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'article']);
    }

    public function getThumbnail()
    {
        return $this->hasOne(Asset::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'article', 'collection_name' => 'thumbnail'])
            ->with('file');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['id' => 'file_id'])->via('assets');
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }
}
