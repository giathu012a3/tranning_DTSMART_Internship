<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\behaviors\UploadAssetBehavior;
use app\models\FileModel;

/**
 * @property Category|null $category
 * @property TagModel[] $tags
 * @property AssetModel[] $assets
 * @property ArticleModel[] $articles
 * @property AssetModel|null $thumbnail
 */
class ProductModel extends Product
{
    public $articles_count;
    public $files_count;

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
        return $this->hasMany(ProductArticleModel::class, ['product_id' => 'id']);
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
        $this->is_deleted = 1;
        return $this->save(false);
    }

    public function getCategoryName()
    {
        return $this->category->name ?? 'N/A';
    }

    public function getArticlesCount()
    {
        return $this->articles_count !== null
            ? (int) $this->articles_count
            : ($this->isRelationPopulated('articles') ? count($this->articles) : (int) $this->getArticles()->count());
    }

    public function getFilesCount()
    {
        return $this->files_count !== null
            ? (int) $this->files_count
            : ($this->isRelationPopulated('assets') ? count($this->assets) : (int) $this->getAssets()->count());
    }
    public function fields()
    {
        return [
            'id',
            'name',
            'price',
            'stock',
            'status',
            'category_id',
            'category_name' => fn() => $this->getCategoryName(),
            'tags' => fn() => array_map(fn($tag) => [
                'id'   => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ], $this->tags),
            'thumbnail_url' => function() {
                if ($this->isRelationPopulated('assets')) {
                    foreach ($this->assets as $asset) {
                        if ($asset->collection_name === 'thumbnail') {
                            return $asset->file ? FileModel::buildUrl($asset->file->file_path) : null;
                        }
                    }
                    return null;
                }
                $thumbnail = $this->isRelationPopulated('thumbnail') ? $this->relatedRecords['thumbnail'] : $this->getThumbnail()->one();
                return $thumbnail && $thumbnail->file ? FileModel::buildUrl($thumbnail->file->file_path) : null;
            },
            'linked_items' => fn() => [
                'files_count'    => $this->getFilesCount(),
                'articles_count' => $this->getArticlesCount(),
            ],
            'created_at' => fn() => $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => fn() => $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null,
        ];
    }

    public function extraFields()
    {
        return [
            'description',
            'category',
            'articles',
            'attachments' => 'assets',
        ];
    }
}
