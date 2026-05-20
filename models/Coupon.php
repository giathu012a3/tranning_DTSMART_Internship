<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coupons".
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property float $value
 * @property float|null $max_amount
 * @property float|null $min_purchase
 * @property int|null $usage_limit
 * @property int $status
 * @property int $start_date
 * @property int $expiry_date
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CouponUsage[] $couponUsages
 */
class Coupon extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['max_amount', 'min_purchase', 'usage_limit'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['code', 'type', 'value', 'start_date', 'expiry_date', 'created_at', 'updated_at'], 'required'],
            [['max_amount', 'min_purchase'], 'number', 'min' => 0],
            [['value'], 'number'],
            [['value'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'Coupon value must be greater than 0.'],
            [['usage_limit', 'status', 'start_date', 'expiry_date', 'created_at', 'updated_at'], 'integer'],
            [['code', 'type'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'type' => 'Type',
            'value' => 'Value',
            'max_amount' => 'Max Amount',
            'min_purchase' => 'Min Purchase',
            'usage_limit' => 'Usage Limit',
            'status' => 'Status',
            'start_date' => 'Start Date',
            'expiry_date' => 'Expiry Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsage::class, ['coupon_id' => 'id']);
    }
}
