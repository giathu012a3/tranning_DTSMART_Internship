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


    public function activeCategory()
    {
        return $this->innerJoinWith('category')
            ->andOnCondition(['categories.status' => 1]);
    }
    public function Category()
    {
        return $this->innerJoinWith('category');
    }

    public function withAsset()
    {
        return $this->with(['assets.file']);
    }

    public function byId($id)
    {
        return $this->andWhere(['products.id' => $id]);
    }
}
