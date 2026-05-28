<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "role_permissions".
 *
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property int $created_at
 * @property int $updated_at
 */
class RolePermission extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'role_permissions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'permission_id', 'created_at', 'updated_at'], 'required'],
            [['role_id', 'permission_id', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'permission_id' => 'Permission ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
