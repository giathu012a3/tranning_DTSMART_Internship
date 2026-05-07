<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property int|null $member_ship_id
 * @property int|null $total_points
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property MembershipLevel $membershipLevel
 * @property Order[] $orders
 * @property Cart $cart
 * @property CouponUsage[] $couponUsages
 * @property ArticleComment[] $articleComments
 * @property ArticleLike[] $articleLikes
 * @property UserAddress[] $userAddresses
 */
class User extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_ship_id'], 'default', 'value' => null],
            [['total_points'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['username', 'email', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['member_ship_id', 'total_points', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'password_hash'], 'string', 'max' => 255],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'member_ship_id' => 'Member Ship ID',
            'total_points' => 'Total Points',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[MembershipLevel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMembershipLevel()
    {
        return $this->hasOne(MembershipLevel::class, ['id' => 'member_ship_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Cart]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CouponUsages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsage::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ArticleComments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleComments()
    {
        return $this->hasMany(ArticleComment::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ArticleLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleLikes()
    {
        return $this->hasMany(ArticleLike::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddress::class, ['user_id' => 'id']);
    }
}
