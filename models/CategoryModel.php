<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\query\CategoriesQuery;

class CategoryModel extends Category
{
    public bool $detailMode = false;
    public $products_count;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function find()
    {
        return new CategoriesQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(ProductModel::class, ['category_id' => 'id'])->notDeleted();
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        $this->is_deleted = 1;
        return $this->save(false);
    }

    public function fields()
    {
        $fields = [
            'id',
            'name',
            'status',
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
            'linked_items' => function () {
                $productsCount = $this->products_count !== null
                    ? $this->products_count
                    : ($this->isRelationPopulated('products') ? count($this->products) : (int) $this->getProducts()->count());
                return [
                    'products_count' => (int) $productsCount,
                ];
            },
        ];

        if ($this->detailMode) {
            $fields = array_merge($fields, $this->extraFields());
        }

        return $fields;
    }

    public function extraFields()
    {
        return [
            'products' => function () {
                $products = $this->isRelationPopulated('products') ? $this->relatedRecords['products'] : $this->getProducts()->all();
                return array_map(fn($product) => [
                    'id'    => $product->id,
                    'name'  => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                ], $products);
            },
        ];
    }
}
