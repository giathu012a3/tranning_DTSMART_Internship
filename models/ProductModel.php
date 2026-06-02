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
    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $articles_count;
    public $tags;
    public array $tagErrors = [];
    private $_currentTags = null;

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

    public function afterFind()
    {
        parent::afterFind();
        $this->tags = $this->getCurrentTags();
    }

    public function getCurrentTags()
    {
        if ($this->_currentTags === null) {
            if ($this->isNewRecord) {
                $this->_currentTags = [];
            } else {
                $this->_currentTags = $this->getTags()->select('name')->column();
            }
        }
        return $this->_currentTags;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        try {
            $tagIds = TagModel::resolveIds($this->tags ?? [], $this->tagErrors);
            TagModel::syncForProduct($this->id, $tagIds);
        } catch (\Throwable $e) {
            $this->tagErrors[] = 'Lỗi hệ thống khi đồng bộ tag: ' . $e->getMessage();
        }
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'price',
            'stock',
            'status',
            'category_id',
            'category_name' => function () {
                return $this->category->name ?? 'N/A';
            },
            'tags' => function () {
                return array_map(fn($tag) => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ], $this->tags);
            },
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
            'linked_items' => function () {
                return [
                    'files_count'    => count($this->assets),
                    'articles_count' => $this->articles_count ?? count($this->articles),
                ];
            },
            'thumbnail_url' => function () {
                foreach ($this->assets as $asset) {
                    if ($asset->collection_name === 'thumbnail' && $asset->file) {
                        return FileModel::buildUrl($asset->file->file_path);
                    }
                }
                return null;
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return [
            'description' => 'description',
            'attachments' => function () {
                return array_map(fn($asset) => [
                    'id'              => $asset->id,
                    'file_id'         => $asset->file->id,
                    'file_name'       => $asset->file->file_name,
                    'file_path'       => $asset->file->file_path,
                    'file_size'       => $asset->file->file_size,
                    'collection_name' => $asset->collection_name,
                    'file_type'       => $asset->file->file_type,
                ], array_filter($this->assets, fn($asset) => $asset->file !== null));
            },
            'articles' => function () {
                return array_map(fn($article) => [
                    'id'      => $article->id,
                    'title'   => $article->title,
                    'slug'    => $article->slug,
                    'excerpt' => $article->excerpt,
                ], $this->articles);
            },
        ];
    }
}
