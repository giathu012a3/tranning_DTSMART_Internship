<?php

namespace app\models\response;

use app\models\Product;

class ProductResponse extends Product
{
    public function fields()
    {
        return [
            'id',
            'name',
            'description',
            'status',
            'price',
            'stock',
            'category_id',
            'category_name' => function ($model) {
                return $model->category ? $model->category->name : 'N/A';
            },
        ];
    }
}