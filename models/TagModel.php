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

    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'status',
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
        ];
    }

    public static function syncForProduct(int $productId, array $tagIds, bool $isInsert = false): void
    {
        if (!$isInsert) {
            ProductTagModel::deleteAll(['product_id' => $productId]);
        }

        if (empty($tagIds)) {
            return;
        }

        $rows = [];
        $expression = new \yii\db\Expression('UNIX_TIMESTAMP()');
        foreach ($tagIds as $id) {
            $rows[] = [$productId, $id, $expression, $expression];
        }

        Yii::$app->db->createCommand()
            ->batchInsert(ProductTagModel::tableName(), ['product_id', 'tag_id', 'created_at', 'updated_at'], $rows)
            ->execute();
    }

    public static function syncForArticle(int $articleId, array $tagIds, bool $isInsert = false): void
    {
        if (!$isInsert) {
            ArticleTagModel::deleteAll(['article_id' => $articleId]);
        }

        if (empty($tagIds)) {
            return;
        }

        $rows = [];
        $expression = new \yii\db\Expression('UNIX_TIMESTAMP()');
        foreach ($tagIds as $id) {
            $rows[] = [$articleId, $id, $expression, $expression];
        }

        Yii::$app->db->createCommand()
            ->batchInsert(ArticleTagModel::tableName(), ['article_id', 'tag_id', 'created_at', 'updated_at'], $rows)
            ->execute();
    }

    public static function resolveIds(array $names, array &$errors = []): array
    {
        $names = array_unique(array_filter(array_map('trim', $names)));
        if (empty($names)) {
            return [];
        }

        $existing = static::find()
            ->where(['in', 'name', $names])
            ->all();

        $existingMap = [];
        foreach ($existing as $tag) {
            $existingMap[mb_strtolower($tag->name)] = $tag;
        }

        $tagIds = [];
        foreach ($names as $name) {
            $lowerName = mb_strtolower($name);
            if (isset($existingMap[$lowerName])) {
                $tagIds[] = (int)$existingMap[$lowerName]->id;
            } else {
                $tag = new static();
                $tag->name = $name;
                if ($tag->save()) {
                    $tagIds[] = (int)$tag->id;
                } else {
                    $errors[] = "Tag '{$name}' failed to save: " . implode(', ', $tag->getFirstErrors());
                }
            }
        }

        return $tagIds;
    }
}
