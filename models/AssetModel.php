<?php

namespace app\models;

class AssetModel extends Asset
{
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
