<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[Cart]].
 *
 * @see Cart
 */
class CartsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Cart[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Cart|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Filter query by Cart ID.
     *
     * @param int $id
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere(['carts.id' => $id]);
    }

    /**
     * Filter query by User ID.
     *
     * @param int $userId
     * @return $this
     */
    public function byUserId($userId)
    {
        return $this->andWhere(['carts.user_id' => $userId]);
    }

    /**
     * Eager load cart details and their product relations.
     *
     * @return $this
     */
    public function withDetails()
    {
        return $this->with(['cartDetails.product']);
    }
}
