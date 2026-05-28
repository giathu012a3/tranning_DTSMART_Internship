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
 * @property string|null $access_token
 * @property int|null $member_ship_id
 * @property int|null $total_points
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
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
            [['access_token', 'member_ship_id'], 'default', 'value' => null],
            [['total_points'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['username', 'email', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['member_ship_id', 'total_points', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'password_hash', 'access_token'], 'string', 'max' => 255],
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
            'access_token' => 'Access Token',
            'member_ship_id' => 'Member Ship ID',
            'total_points' => 'Total Points',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
