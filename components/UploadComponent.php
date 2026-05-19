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
        $assetType = strtolower((new \ReflectionClass($model))->getShortName());
        $time = time();

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
                $filesData = [];
                $filePaths = [];

                foreach ($files as $file) {
                    $fileName = time() . '_' . Yii::$app->security->generateRandomString(5) . '.' . $file->extension;
                    $relativePath = 'uploads/' . $folder . '/' . $fileName;
                    $absolutePath = Yii::getAlias('@webroot/') . $relativePath;

                    if (!is_dir(dirname($absolutePath))) {
                        if (!mkdir(dirname($absolutePath), 0777, true)) {
                            throw new \Exception("Cannot create directory: " . dirname($absolutePath));
                        }
                    }

                    if ($file->saveAs($absolutePath)) {
                        $filesData[] = [
                            $relativePath,
                            $fileName,
                            $file->type,
                            $file->size,
                            1, // status
                            $time,
                            $time
                        ];
                        $filePaths[] = $relativePath;
                    } else {
                        throw new \Exception("Cannot save as physical file. error (code): " . $file->error);
                    }
                }

                if (!empty($filesData)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        File::tableName(), 
                        ['file_path', 'file_name', 'file_type', 'file_size', 'status', 'created_at', 'updated_at'], 
                        $filesData
                    )->execute();

                    $insertedFiles = File::find()->where(['in', 'file_path', $filePaths])->all();

                    $assetsData = [];
                    foreach ($insertedFiles as $insertedFile) {
                        $assetsData[] = [
                            $model->id,
                            $assetType,
                            $insertedFile->id,
                            $attribute,
                            $time,
                            $time
                        ];
                    }

                    if (!empty($assetsData)) {
                        Yii::$app->db->createCommand()->batchInsert(
                            Asset::tableName(),
                            ['asset_id', 'asset_type', 'file_id', 'collection_name', 'created_at', 'updated_at'],
                            $assetsData
                        )->execute();
                    }
                }
            }
        }
    }
}
