<?php

namespace app\models;

use Yii;

class TagModel extends Tag
{
    public static function syncForProduct(int $productId, array $tagIds): void
    {
        ProductTag::deleteAll(['product_id' => $productId]);

        if (empty($tagIds)) {
            return;
        }

        $time = time();
        $rows = array_map(fn($id) => [$productId, $id, $time, $time], $tagIds);

        Yii::$app->db->createCommand()
            ->batchInsert(ProductTag::tableName(), ['product_id', 'tag_id', 'created_at', 'updated_at'], $rows)
            ->execute();
    }
}
