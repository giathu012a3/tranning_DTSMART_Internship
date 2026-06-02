<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * @property FileModel|null $file
 */
class AssetModel extends Asset
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    public function getFile()
    {
        return $this->hasOne(FileModel::class, ['id' => 'file_id']);
    }
}
