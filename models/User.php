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
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
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
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
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
            [['access_token'], 'string', 'max' => 255],
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
            'access_token' => 'Access Token',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMembershipLevel()
    {
        return $this->hasOne(MembershipLevel::class, ['id' => 'member_ship_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsage::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleComments()
    {
        return $this->hasMany(ArticleComment::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleLikes()
    {
        return $this->hasMany(ArticleLike::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddress::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Role::class, ['id' => 'role_id'])->via('userRoles');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permission::class, ['id' => 'permission_id'])->via('roles');
    }   

    // =========================================================
    // IdentityInterface - Required for Yii2 Auth
    // =========================================================

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => 1]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->access_token;
    }

    public function validateAuthKey($authKey)
    {
        return $this->access_token === $authKey;
    }

    public function updateMembershipLevel()
    {
        $newLevel = MembershipLevel::find()
            ->active()
            ->byPoints($this->total_points)
            ->one();

        if ($newLevel && $newLevel->id !== $this->member_ship_id) {
            $this->member_ship_id = $newLevel->id;
        }
    }
}
