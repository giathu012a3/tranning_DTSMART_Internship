<?php

namespace app\models;

use app\models\query\ProductsQuery;
use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property float $stock
 * @property int $status
 * @property string|null $description
 * @property int $category_id
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 * @property int $is_deleted
 */
class Product extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'deleted_at'], 'default', 'value' => null],
            [['status', 'is_deleted'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['name', 'price', 'stock', 'category_id'], 'required'],
            [['price', 'stock'], 'number'],
            [['status', 'category_id', 'created_at', 'updated_at', 'deleted_at', 'is_deleted'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'price' => 'Price',
            'stock' => 'Stock',
            'status' => 'Status',
            'description' => 'Description',
            'category_id' => 'Category ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return ProductsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProductsQuery(get_called_class());
    }
}
