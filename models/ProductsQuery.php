<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Product]].
 *
 * @see Product
 */
class ProductsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere('products.status=1');
    }

    public function notDeleted()
    {
        return $this->andWhere(['products.deleted_at' => null]);
    }

    /**
     * {@inheritdoc}
     * @return Product[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Product|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
    public function withAsset()
    {
        return $this->with(['assets' => function ($q) {
            $q->select(['id', 'asset_id', 'file_id', 'collection_name', 'asset_type']);
        }, 'assets.file' => function ($q) {
            $q->select(['id', 'file_path', 'file_name', 'file_type', 'file_size']);
        }]);
    }


    public function withTags()
    {
        return $this->with([
            'tags' => function ($q) {
                $q->select(['id', 'name', 'slug']);
            }
        ]);
    }

    public function withCategory()
    {
        return $this->with([
            'category' => function ($q) {
                $q->select(['id', 'name'])->andOnCondition(['categories.deleted_at' => null]);
            }
        ]);
    }

    public function withArticles()
    {
        return $this->with([
            'articles' => function ($q) {
                $q->select(['id', 'title', 'slug', 'excerpt']);
            }
        ]);
    }

    public function byId($id)
    {
        return $this->andWhere(['products.id' => $id]);
    }
}
