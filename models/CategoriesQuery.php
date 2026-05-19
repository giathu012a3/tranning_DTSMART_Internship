<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Category]].
 *
 * @see Category
 */
class CategoriesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['deleted_at' => null]);
    }

    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
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
