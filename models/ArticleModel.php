<?php

namespace app\models;

use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use app\behaviors\UploadAssetBehavior;
use app\models\query\ArticlesQuery;

/**
 * @property User|null $author
 * @property TagModel[] $tags
 * @property AssetModel[] $assets
 * @property ProductModel[] $products
 * @property AssetModel|null $thumbnail
 */
class ArticleModel extends Article
{
    public static function find()
    {
        return new ArticlesQuery(get_called_class());
    }

    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $comment_count;
    public $tags;
    public array $tagErrors = [];
    public bool $detailMode = false;

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
        return $this->hasMany(TagModel::class, ['id' => 'tag_id'])->via('articleTags');
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
        return $this->hasMany(ProductModel::class, ['id' => 'product_id'])->via('productArticles')->notDeleted();
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
        return $this->hasMany(AssetModel::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'article']);
    }

    public function getThumbnail()
    {
        return $this->hasOne(AssetModel::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'article', 'collection_name' => 'thumbnail']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(FileModel::class, ['id' => 'file_id'])->via('assets');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->tags !== null) {
            try {
                $tagIds = TagModel::resolveIds($this->tags, $this->tagErrors);
                TagModel::syncForArticle($this->id, $tagIds, $insert);
            } catch (\Throwable $e) {
                $this->tagErrors[] = 'Lỗi hệ thống khi đồng bộ tag: ' . $e->getMessage();
            }
        }
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'slug',
            'status',
            'author_id',
            'author_name' => function () {
                return $this->author->username ?? 'N/A';
            },
            'stats' => function () {
                $commentCount = $this->comment_count !== null
                    ? (int) $this->comment_count
                    : ($this->isRelationPopulated('articleComments') ? count($this->articleComments) : (int) $this->getArticleComments()->count());

                return [
                    'view_count' => 0,
                    'like_count' => (int) ($this->like_count ?? 0),
                    'comment_count' => $commentCount,
                ];
            },
            'tags' => function () {
                $tagModels = $this->isRelationPopulated('tags') ? $this->relatedRecords['tags'] : $this->getTags()->all();
                return array_map(fn($tag) => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ], $tagModels);
            },
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
            'linked_items' => function () {
                return [
                    'files_count'    => $this->isRelationPopulated('assets') ? count($this->assets) : (int) $this->getAssets()->count(),
                    'products_count' => $this->isRelationPopulated('products') ? count($this->products) : (int) $this->getProducts()->count(),
                ];
            },
        ];

        if ($this->excerpt !== null) {
            $fields['excerpt'] = 'excerpt';
        }

        if ($this->detailMode) {
            $fields = array_merge($fields, $this->extraFields());
        } else {
            $fields['thumbnail_url'] = function () {
                foreach ($this->assets as $asset) {
                    if ($asset->collection_name === 'thumbnail' && $asset->file) {
                        return FileModel::buildUrl($asset->file->file_path);
                    }
                }
                return null;
            };
        }

        return $fields;
    }

    public function extraFields()
    {
        return [
            'content' => 'content',
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
            'products' => function () {
                return array_map(fn($product) => [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'price'       => $product->price,
                    'status'      => $product->status,
                    'category_id' => $product->category_id,
                ], $this->products);
            },
        ];
    }
}
