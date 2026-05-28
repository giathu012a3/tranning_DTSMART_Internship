<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

class OrderWithRelations extends Order
{
    public $items_count;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMembershipLevel()
    {
        return $this->hasOne(MembershipLevel::class, ['id' => 'membership_level_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetail::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsage()
    {
        return $this->hasOne(CouponUsage::class, ['order_id' => 'id']);
    }

    public function softDelete(): bool
    {
        $this->deleted_at = time();
        return $this->save(false);
    }
}
