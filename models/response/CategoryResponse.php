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

    /**
     * Factory method to create CategoryResponse from a Category model.
     *
     * @param Category $category
     * @return self
     */
    public static function fromModel(Category $category)
    {
        $response = new self();
        self::populateRecord($response, $category->attributes);

        return $response;
    }
}
