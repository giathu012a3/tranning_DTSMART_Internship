<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[CartDetail]].
 *
 * @see CartDetail
 */
class CartDetailsQuery extends \yii\db\ActiveQuery
{
    /**
     * Filter query by Cart ID.
     *
     * @param int $cartId
     * @return $this
     */
    public function byCartId($cartId)
    {
        return $this->andWhere(['cart_details.cart_id' => $cartId]);
    }

    /**
     * Filter query by Product ID.
     *
     * @param int $productId
     * @return $this
     */
    public function byProductId($productId)
    {
        return $this->andWhere(['cart_details.product_id' => $productId]);
    }

    /**
     * {@inheritdoc}
     * @return CartDetail[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CartDetail|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
