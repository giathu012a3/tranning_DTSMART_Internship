<?php

namespace app\models;

use app\behaviors\UploadAssetBehavior;
use Override;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "articles".
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $slug
 * @property string $excerpt
 * @property int|null $like_count
 * @property int $author_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $author
 * @property ArticleComment[] $articleComments
 * @property ArticleLike[] $articleLikes
 * @property ArticleTag[] $articleTags
 * @property Tag[] $tags
 * @property ProductArticle[] $productArticles
 */
class Article extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public $deleted_image_ids;
    public $thumbnail;
    public $image;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at'], 'default', 'value' => null],
            [['like_count'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['title', 'content', 'slug', 'excerpt', 'author_id'], 'required'],
            [['content'], 'string'],
            [['like_count', 'author_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['title', 'slug', 'excerpt'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['thumbnail'], 'required', 'on' => self::SCENARIO_CREATE, 'message' => 'Article thumbnail is required.'],
            [['thumbnail'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg, png, webp', 'maxSize' => 5242880],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg, png, webp', 'maxSize' => 5242880, 'maxFiles' => 10],
        ];
    }

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
                    'image'     => 'article_gallery',
                ],
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
            'title' => 'Title',
            'content' => 'Content',
            'slug' => 'Slug',
            'excerpt' => 'Excerpt',
            'like_count' => 'Like Count',
            'author_id' => 'Author ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
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
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productArticles')->active();
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
            ->onCondition(['asset_type' => 'article'])
            ->with('file');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['id' => 'file_id'])->via('assets');
    }
    /**
     * {@inheritdoc}
     * @return ArticlesQuery the active query used by this AR class.
     */
    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }

    public static function find()
    {
        return new ArticlesQuery(get_called_class());
    }

}
