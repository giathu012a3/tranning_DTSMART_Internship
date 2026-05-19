<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Article]].
 *
 * @see Article
 */
class ArticlesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere('[[status]]=1');
    }

    public function notDeleted()
    {
        return $this->andWhere(['articles.deleted_at' => null]);
    }

    /**
     * {@inheritdoc}
     * @return Article[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Article|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byId($id)
    {
        return $this->andWhere(['articles.id' => $id]);
    }

    public function withAsset()
    {
        return $this->with(['assets.file']);
    }
}
