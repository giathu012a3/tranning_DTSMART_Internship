<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\query\CategoriesQuery;

class CategoryModel extends Category
{
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
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
        ];
    }
}
