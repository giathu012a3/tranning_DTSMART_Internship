<?php

namespace app\models;

/**
 * @property FileModel|null $file
 */
class AssetModel extends Asset
{
    public function getFile()
    {
        return $this->hasOne(FileModel::class, ['id' => 'file_id']);
    }
}
