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

    public function fields()
    {
        return [
            'id',
            'file_id',
            'collection_name',
            'file_name' => fn() => $this->file->file_name ?? null,
            'file_path' => fn() => $this->file->file_path ?? null,
            'file_size' => fn() => $this->file->file_size ?? null,
            'file_type' => fn() => $this->file->file_type ?? null,
            'url' => fn() => $this->file ? FileModel::buildUrl($this->file->file_path) : null,
        ];
    }
}
