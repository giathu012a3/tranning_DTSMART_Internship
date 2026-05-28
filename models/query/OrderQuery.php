<?php

namespace app\models\query;

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
        return $this->with([
            'orderDetails' => function ($query) {
                $query->select(['id', 'order_id', 'product_id', 'quantity', 'price']);
            },
            'orderDetails.product' => function ($query) {
                $query->select(['id', 'name']);
            }
        ]);
    }

    public function withCoupon()
    {
        return $this->with([
            'couponUsage' => function ($query) {
                $query->select(['id', 'order_id', 'applied_code', 'applied_type', 'applied_value', 'applied_max_amount']);
            }
        ]);
    }
}
