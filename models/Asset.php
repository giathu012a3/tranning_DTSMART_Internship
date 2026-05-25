<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "assets".
 *
 * @property int $id
 * @property int $file_id
 * @property int $asset_id
 * @property string $asset_type
 * @property string $collection_name
 * @property int $created_at
 * @property int $updated_at
 */
class Asset extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id', 'asset_id', 'asset_type', 'collection_name'], 'required'],
            [['file_id', 'asset_id', 'created_at', 'updated_at'], 'integer'],
            [['asset_type', 'collection_name'], 'string', 'max' => 255],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_id' => 'File ID',
            'asset_id' => 'Asset ID',
            'asset_type' => 'Asset Type',
            'collection_name' => 'Collection Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
