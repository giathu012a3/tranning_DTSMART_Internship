<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;
use app\models\File;
use app\models\Asset;

class UploadComponent extends Component
{

    public function processUploads($model, $attributes = [], $assetType = null)
    {
        if ($assetType === null) {
            $assetType = method_exists($model, 'tableName')
                ? rtrim($model->tableName(), 's')
                : strtolower((new \ReflectionClass($model))->getShortName());
        }
        $time = time();

        $allFilesData = [];
        $allFilePaths = [];
        $pathAttributeMap = [];

        foreach ($attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (empty($files)) {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) {
                    $files = [$file];
                }
            }

            if (!empty($files)) {
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
                        $allFilesData[] = [
                            $relativePath,
                            $fileName,
                            $file->type,
                            $file->size,
                            1,
                            $time,
                            $time
                        ];
                        $allFilePaths[] = $relativePath;
                        $pathAttributeMap[$relativePath] = $attribute;
                    } else {
                        throw new \Exception("Cannot save as physical file. error (code): " . $file->error);
                    }
                }
            }
        }

        if (!empty($allFilesData)) {
            Yii::$app->db->createCommand()->batchInsert(
                File::tableName(),
                [
                    'file_path',
                    'file_name',
                    'file_type',
                    'file_size',
                    'status',
                    'created_at',
                    'updated_at'
                ],
                $allFilesData
            )->execute();

            $insertedFiles = File::find()->where(['in', 'file_path', $allFilePaths])->all();

            $assetsData = [];
            foreach ($insertedFiles as $insertedFile) {
                $attribute = $pathAttributeMap[$insertedFile->file_path] ?? 'default';
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
                    [
                        'asset_id',
                        'asset_type',
                        'file_id',
                        'collection_name',
                        'created_at',
                        'updated_at'
                    ],
                    $assetsData
                )->execute();
            }
        }
    }
}
