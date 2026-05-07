<?php

namespace app\models;

use Yii;

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
            [['like_count'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['title', 'content', 'slug', 'excerpt', 'author_id', 'created_at', 'updated_at'], 'required'],
            [['content'], 'string'],
            [['like_count', 'author_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['title', 'slug', 'excerpt'], 'string', 'max' => 255],
            [['slug'], 'unique'],
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
        ];
    }

    /**
     * Gets query for [[ArticleComments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleComments()
    {
        return $this->hasMany(ArticleComment::class, ['article_id' => 'id']);
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[ArticleLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleLikes()
    {
        return $this->hasMany(ArticleLike::class, ['article_id' => 'id']);
    }

    /**
     * Gets query for [[ArticleTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleTags()
    {
        return $this->hasMany(ArticleTag::class, ['article_id' => 'id']);
    }

    /**
     * Gets query for [[Tags]] via article_tags.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('articleTags');
    }

    /**
     * Gets query for [[ProductArticles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductArticles()
    {
        return $this->hasMany(ProductArticle::class, ['article_id' => 'id']);
    }

}
