<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_comments".
 *
 * @property int $id
 * @property int $article_id
 * @property int $user_id
 * @property string $content
 * @property int|null $parent_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class ArticleComment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_comments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['article_id', 'user_id', 'content', 'created_at', 'updated_at'], 'required'],
            [['article_id', 'user_id', 'parent_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['article_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleModel::class, 'targetAttribute' => ['article_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => 'Article ID',
            'user_id' => 'User ID',
            'content' => 'Content',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
     }
     
}
