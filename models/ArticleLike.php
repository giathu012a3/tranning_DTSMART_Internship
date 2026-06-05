<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_likes".
 *
 * @property int $id
 * @property int $article_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 */
class ArticleLike extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_likes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['article_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'article_id',
            'user_id',
            'created_at' => fn() => $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => fn() => $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null,
        ];
    }
}
