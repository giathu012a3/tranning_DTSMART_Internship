<?php

namespace app\models;

use Yii;

class TagModel extends Tag
{
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1],
            [['name'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::class,
            [
                'class' => \yii\behaviors\SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
        ];
    }

    public static function syncForProduct(int $productId, array $tagIds): void
    {
        ProductTagModel::deleteAll(['product_id' => $productId]);

        if (empty($tagIds)) {
            return;
        }

        $time = time();
        $rows = array_map(fn($id) => [$productId, $id, $time, $time], $tagIds);

        Yii::$app->db->createCommand()
            ->batchInsert(ProductTagModel::tableName(), ['product_id', 'tag_id', 'created_at', 'updated_at'], $rows)
            ->execute();
    }
}
