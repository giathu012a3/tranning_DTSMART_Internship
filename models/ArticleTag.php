<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_tags".
 *
 * @property int $id
 * @property int $article_id
 * @property int $tag_id
 * @property int $created_at
 * @property int $updated_at
 */
class ArticleTag extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'tag_id', 'created_at', 'updated_at'], 'required'],
            [['article_id', 'tag_id', 'created_at', 'updated_at'], 'integer'],
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
            'tag_id' => 'Tag ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
