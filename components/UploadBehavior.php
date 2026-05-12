<?php

namespace app\components;

use app\models\Asset;
use app\models\File;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use Yii;

class UploadBehavior extends Behavior
{
    public $attribute = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave'
        ];
    }

    public function afterSave($events)
    {
        foreach ($this->attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (empty($files)) {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) $file = [$file];
            }
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                $this->saveToSyStem($file, $folder, $attribute);
            }
        }
    }

    public function saveToSyStem($file, $folder, $collectionName)
    {
        $fileName = time() . '_' . Yii::$app->security->generateRandomString(5) . '.' . $file->extension;
        $relativePath = 'uploads/' . $folder . '/' . $fileName;
        $absolutePath = Yii::getAlias('@webroot/') . $relativePath;

        if (!is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0777, true);
        }

        if ($file->saveAs($absolutePath)) {
            $fileModel = new File();
            $fileModel->path = $relativePath;
            $fileModel->file_type = $file->type;

            if ($fileModel->save()) {
                $asset = new Asset();
                $asset->asset_id = $this->owner->id;
                $asset->file_id = $fileModel->id;
                $asset->collection_name = $collectionName;
                $asset->save();
            }
        }
    }
}
