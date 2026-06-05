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

    public $comment_count;
    public $products_count;
    public $files_count;

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
        return $this->hasMany(ProductArticleModel::class, ['article_id' => 'id']);
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

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }

    public function getAuthorName()
    {
        return $this->author->username ?? 'N/A';
    }

    public function getCommentCount()
    {
        return $this->comment_count !== null
            ? (int) $this->comment_count
            : ($this->isRelationPopulated('articleComments') ? count($this->articleComments) : (int) $this->getArticleComments()->count());
    }

    public function getFilesCount()
    {
        return $this->files_count !== null
            ? (int) $this->files_count
            : ($this->isRelationPopulated('assets') ? count($this->assets) : (int) $this->getAssets()->count());
    }

    public function getProductsCount()
    {
        return $this->products_count !== null
            ? (int) $this->products_count
            : ($this->isRelationPopulated('products') ? count($this->products) : (int) $this->getProducts()->count());
    }
    public function fields()
    {
        $fields = [
            'id',
            'title',
            'slug',
            'status',
            'author_id',
            'author_name' => fn() => $this->getAuthorName(),
            'stats' => fn() => [
                'view_count' => 0,
                'like_count' => (int) ($this->like_count ?? 0),
                'comment_count' => $this->getCommentCount(),
            ],
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
                'products_count' => $this->getProductsCount(),
            ],
            'created_at' => fn() => $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => fn() => $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null,
        ];

        if ($this->excerpt !== null) {
            $fields['excerpt'] = 'excerpt';
        }

        return $fields;
    }

    public function extraFields()
    {
        return [
            'content',
            'author',
            'products',
            'attachments' => 'assets',
            'comments' => 'articleComments',
            'likes' => 'articleLikes',
        ];
    }
}
