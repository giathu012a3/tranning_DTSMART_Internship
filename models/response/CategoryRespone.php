<?php

namespace app\models\response;

use app\models\Category;


class CategoryRespone extends Category
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
