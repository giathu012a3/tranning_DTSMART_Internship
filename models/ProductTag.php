<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_tags".
 *
 * @property int $id
 * @property int $product_id
 * @property int $tag_id
 * @property int $created_at
 * @property int $updated_at
 */
class ProductTag extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'tag_id', 'created_at', 'updated_at'], 'required'],
            [['product_id', 'tag_id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'tag_id' => 'Tag ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
