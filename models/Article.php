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
 * @property int|null $deleted_at
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
            [['deleted_at'], 'default', 'value' => null],
            [['like_count'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['title', 'content', 'slug', 'excerpt', 'author_id', 'created_at', 'updated_at'], 'required'],
            [['content'], 'string'],
            [['like_count', 'author_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
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
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return ArticlesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ArticlesQuery(get_called_class());
    }
}
