<?php

namespace app\models\response;

use app\models\Category;


class CategoryResponse extends Category
{
    public function fields()
    {
        return [
            'id',
            'name',
            'created_at',
        ];
    }
}
