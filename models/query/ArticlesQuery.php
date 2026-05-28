<?php

namespace app\models\query;

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
        return $this->with([
            'assets' => function ($q) {
                $q->select(['id', 'asset_id', 'file_id', 'collection_name', 'asset_type']);
            },
            'assets.file' => function ($q) {
                $q->select(['id', 'file_path', 'file_name', 'file_type', 'file_size']);
            }
        ]);
    }

    public function withTags()
    {
        return $this->with([
            'tags' => function ($q) {
                $q->select(['id', 'name', 'slug']);
            }
        ]);
    }

    public function withAuthor()
    {
        return $this->with([
            'author' => function ($q) {
                $q->select(['id', 'username', 'email']);
            }
        ]);
    }

    public function withProducts()
    {
        return $this->with([
            'products' => function ($q) {
                $q->select(['products.id', 'products.name', 'products.price', 'products.status', 'products.category_id'])->active();
            }
        ]);
    }
}
