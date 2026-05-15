<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Order]].
 *
 * @see Order
 */
class OrderQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['orders.status' => 1]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['orders.deleted_at' => null]);
    }

    /**
     * {@inheritdoc}
     * @return Order[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Order|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byId($id)
    {
        return $this->andWhere(['orders.id' => $id]);
    }

    public function byUser($userId)
    {
        return $this->andWhere(['orders.user_id' => $userId]);
    }

    public function byStatus($status)
    {
        return $this->andWhere(['orders.status' => $status]);
    }

    public function withDetails()
    {
        return $this->with(['orderDetails', 'orderDetails.product']);
    }

    public function withCoupon()
    {
        return $this->with(['couponUsage']);
    }
}
