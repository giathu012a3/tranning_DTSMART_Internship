<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[Category]].
 *
 * @see Category
 */
class CategoriesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['categories.status' => 1]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['categories.is_deleted' => 0]);
    }

    public function withProducts()
    {
        return $this->with([
            'products' => function ($q) {
                $q->select(['id', 'name', 'price', 'stock', 'category_id'])->notDeleted();
            }
        ]);
    }

    public function byId($id)
    {
        return $this->andWhere(['categories.id' => $id]);
    }

    /**
     * {@inheritdoc}
     * @return Category[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Category|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
