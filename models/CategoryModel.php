<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\query\CategoriesQuery;

class CategoryModel extends Category
{
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
        return [
            'id',
            'name',
            'status',
            'linked_items' => fn() => [
                'products_count' => (int) ($this->products_count !== null
                    ? $this->products_count
                    : ($this->isRelationPopulated('products') ? count($this->products) : (int) $this->getProducts()->count())),
            ],
            'created_at' => fn() => $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => fn() => $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null,
        ];
    }

    public function extraFields()
    {
        return [
            'products',
        ];
    }
}
