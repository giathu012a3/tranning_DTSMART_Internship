<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "membership_levels".
 *
 * @property int $id
 * @property string $name
 * @property int $points_required
 * @property float $discount_rate
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User[] $users
 * @property Order[] $orders
 */
class MembershipLevel extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'membership_levels';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['points_required'], 'default', 'value' => 0],
            [['discount_rate'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 1],
            [['name', 'created_at', 'updated_at'], 'required'],
            [['points_required', 'status', 'created_at', 'updated_at'], 'integer'],
            [['discount_rate'], 'number'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'points_required' => 'Points Required',
            'discount_rate' => 'Discount Rate',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['member_ship_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['membership_level_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MembershipLevelsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MembershipLevelsQuery(get_called_class());
    }
}
