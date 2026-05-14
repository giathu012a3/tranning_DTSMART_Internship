<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;
use app\models\File;
use app\models\Asset;

class UploadComponent extends Component
{

    public function processUploads($model, $attributes = [])
    {
        foreach ($attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (empty($files)) {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) $files = [$file];
            }

            // Fallback for form models
            if (empty($files)) {
                $files = UploadedFile::getInstances($model, $attribute);
                if (empty($files)) {
                    $file = UploadedFile::getInstance($model, $attribute);
                    if ($file) $files = [$file];
                }
            }

            if (!empty($files)) {
                foreach ($files as $file) {
                    $this->saveToSystem($file, $folder, $attribute, $model);
                }
            }
        }
    }

    protected function saveToSystem($file, $folder, $collectionName, $model)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file->extension), $allowedExtensions)) {
            throw new \Exception("Error: Only allow uploading image (jpg, png, webp...). Your file is: " . $file->extension);
        }

        if ($file->size > 5242880) {
            throw new \Exception("The file size is too large! Please upload the image below 5MB.");
        }

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
                throw new \Exception("Table saving error File: " . json_encode($fileModel->getErrors()));
            }

            $asset = new Asset();
            $asset->asset_id = $model->id;
            $asset->asset_type = strtolower((new \ReflectionClass($model))->getShortName());
            $asset->file_id = $fileModel->id;
            $asset->collection_name = $collectionName;

            if (!$asset->save()) {
                throw new \Exception("Table saving error Asset: " . json_encode($asset->getErrors()));
            }
        } else {
            throw new \Exception("Cannot save as physical file. error (code): " . $file->error);
        }
    }
}
