<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\behaviors\UploadAssetBehavior;

/**
 * @property Category|null $category
 * @property TagModel[] $tags
 * @property AssetModel[] $assets
 * @property ArticleModel[] $articles
 * @property AssetModel|null $thumbnail
 */
class ProductModel extends Product
{
    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $articles_count;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => UploadAssetBehavior::class,
                'attributes' => [
                    'thumbnail' => 'products',
                    'images'    => 'product_gallery',
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
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductArticles()
    {
        return $this->hasMany(ProductArticle::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(ArticleModel::class, ['id' => 'article_id'])->via('productArticles')->notDeleted();
    }

    public function getActiveArticles()
    {
        return $this->getArticles()->active();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCartDetails()
    {
        return $this->hasMany(CartDetail::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetail::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(AssetModel::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'product']);
    }

    public function getThumbnail()
    {
        return $this->hasOne(AssetModel::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'product', 'collection_name' => 'thumbnail']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['id' => 'file_id'])->via('assets');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTags()
    {
        return $this->hasMany(ProductTagModel::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(TagModel::class, ['id' => 'tag_id'])->via('productTags');
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }
}
