<?php

namespace app\models;

use Override;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "coupon_usages".
 *
 * @property int $id
 * @property int $coupon_id
 * @property int $user_id
 * @property int $order_id
 * @property string $applied_code
 * @property string $applied_type
 * @property float $applied_value
 * @property float|null $applied_max_amount
 * @property int $created_at
 *
 * @property Coupon $coupon
 * @property User $user
 * @property Order $order
 */
class CouponUsage extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon_usages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applied_max_amount'], 'default', 'value' => null],
            [['coupon_id', 'user_id', 'order_id', 'applied_code', 'applied_type', 'applied_value'], 'required'],
            [['coupon_id', 'user_id', 'order_id', 'created_at'], 'integer'],
            [['applied_value', 'applied_max_amount'], 'number'],
            [['applied_code', 'applied_type'], 'string', 'max' => 255],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_id' => 'Coupon ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'applied_code' => 'Applied Code',
            'applied_type' => 'Applied Type',
            'applied_value' => 'Applied Value',
            'applied_max_amount' => 'Applied Max Amount',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Coupon]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupon::class, ['id' => 'coupon_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
