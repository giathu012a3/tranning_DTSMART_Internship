<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;
use app\models\FileModel;
use app\models\AssetModel;

class UploadComponent extends Component
{

    public function processUploads($model, $attributes = [], $assetType = null)
    {
        if ($assetType === null) {
            $assetType = method_exists($model, 'tableName')
                ? rtrim($model->tableName(), 's')
                : strtolower((new \ReflectionClass($model))->getShortName());
        }
        foreach ($attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (empty($files)) {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) {
                    $files = [$file];
                }
            }

            if (!empty($files)) {
                $targetDir = Yii::getAlias('@webroot/uploads/' . $folder);
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0777, true)) {
                        Yii::error("Cannot create directory: " . $targetDir, __METHOD__);
                        continue;
                    }
                }

                foreach ($files as $file) {
                    try {
                        $fileName = time() . '_' . Yii::$app->security->generateRandomString(5) . '.' . $file->extension;
                        $relativePath = 'uploads/' . $folder . '/' . $fileName;
                        $absolutePath = Yii::getAlias('@webroot/') . $relativePath;

                        if (!$file->saveAs($absolutePath)) {
                            throw new \Exception("Cannot save physical file '{$fileName}' (Error code: {$file->error}).");
                        }

                        $fileModel = new FileModel();
                        $fileModel->file_path = $relativePath;
                        $fileModel->file_name = $fileName;
                        $fileModel->file_type = $file->type;
                        $fileModel->file_size = $file->size;
                        $fileModel->status = 1;

                        if (!$fileModel->save()) {
                            if (file_exists($absolutePath)) {
                                @unlink($absolutePath);
                            }
                            throw new \Exception("DB Error saving File record: " . implode(', ', $fileModel->getFirstErrors()));
                        }

                        $asset = new AssetModel();
                        $asset->asset_id = $model->id;
                        $asset->asset_type = $assetType;
                        $asset->file_id = $fileModel->id;
                        $asset->collection_name = $attribute;

                        if (!$asset->save()) {
                            $fileModel->delete();
                            if (file_exists($absolutePath)) {
                                @unlink($absolutePath);
                            }
                            throw new \Exception("DB Error saving Asset record: " . implode(', ', $asset->getFirstErrors()));
                        }
                    } catch (\Throwable $e) {
                        Yii::error("Error processing upload for file: " . $file->name . ". Error: " . $e->getMessage(), __METHOD__);
                        $model->addError($attribute, "Ảnh '{$file->name}' tải lên thất bại: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
