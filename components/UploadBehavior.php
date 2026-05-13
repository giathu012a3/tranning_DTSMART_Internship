<?php

namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use app\models\File;
use app\models\Asset;

class UploadBehavior extends Behavior
{
    public $attributes = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave'
        ];
    }

    public function afterSave($event)
    {
        foreach ($this->attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (empty($files)) {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) $files = [$file];
            }
            
            if (empty($files)) {
                $files = UploadedFile::getInstances($this->owner, $attribute);
                if (empty($files)) {
                    $file = UploadedFile::getInstance($this->owner, $attribute);
                    if ($file) $files = [$file];
                }
            }

            if (!empty($files)) {
                foreach ($files as $file) {
                    $this->saveToSystem($file, $folder, $attribute);
                }
            }
        }
    }

    public function saveToSystem($file, $folder, $collectionName)
    {
        $fileName = time() . '_' . Yii::$app->security->generateRandomString(5) . '.' . $file->extension;
        $relativePath = 'uploads/' . $folder . '/' . $fileName;
        $absolutePath = Yii::getAlias('@webroot/') . $relativePath;

        if (!is_dir(dirname($absolutePath))) {
            if (!mkdir(dirname($absolutePath), 0777, true)) { 
                throw new \Exception("Cannot create directory: " . dirname($absolutePath));
            }
        }

        if ($file->saveAs($absolutePath)) {
            $fileModel = new File();
            $fileModel->file_path = $relativePath;
            $fileModel->file_name = $fileName;
            $fileModel->file_type = $file->type;
            $fileModel->file_size = $file->size;

            if (!$fileModel->save()) {
                throw new \Exception("Lỗi lưu bảng File: " . json_encode($fileModel->getErrors()));
            }

            $asset = new Asset();
            $asset->asset_id = $this->owner->id;
            $asset->asset_type = strtolower((new \ReflectionClass($this->owner))->getShortName());
            $asset->file_id = $fileModel->id;
            $asset->collection_name = $collectionName;
            
            if (!$asset->save()) {
                throw new \Exception("Lỗi lưu bảng Asset: " . json_encode($asset->getErrors()));
            }
        } else {
            throw new \Exception("Không thể saveAs file vật lý. Lỗi (code): " . $file->error);
        }
    }
}
