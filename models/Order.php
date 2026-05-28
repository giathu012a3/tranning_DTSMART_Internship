<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property int|null $membership_level_id
 * @property float|null $membership_discount_rate
 * @property float $discount_amount
 * @property float $total
 * @property float $final_total
 * @property string $payment_method
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 */
class Order extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['membership_level_id', 'deleted_at'], 'default', 'value' => null],
            [['membership_discount_rate'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 1],
            [['user_id', 'full_name', 'email', 'phone', 'address', 'discount_amount', 'total', 'final_total', 'payment_method', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'membership_level_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['membership_discount_rate', 'discount_amount', 'total', 'final_total'], 'number'],
            [['full_name', 'email', 'phone', 'address', 'payment_method'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'membership_level_id' => 'Membership Level ID',
            'membership_discount_rate' => 'Membership Discount Rate',
            'discount_amount' => 'Discount Amount',
            'total' => 'Total',
            'final_total' => 'Final Total',
            'payment_method' => 'Payment Method',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }
}
