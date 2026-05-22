<?php

namespace app\behaviors;

use app\models\Asset;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class UploadAssetBehavior extends Behavior
{
    public $attributes = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    public function beforeValidate($event)
    {
        $model = $this->owner;
        foreach ($this->attributes as $attribute => $folder) {
            $files = UploadedFile::getInstancesByName($attribute);
            if (!empty($files)) {
                $model->$attribute = $files;
            } else {
                $file = UploadedFile::getInstanceByName($attribute);
                if ($file) {
                    $model->$attribute = $file;
                }
            }
        }
    }

    public function afterSave($event)
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;
        $assetType = strtolower((new \ReflectionClass($model))->getShortName());

        $isInsert = $event->name === ActiveRecord::EVENT_AFTER_INSERT;

        if (!$isInsert) {
            $newThumbnail = UploadedFile::getInstanceByName('thumbnail');
            if ($newThumbnail) {
                Asset::deleteAll([
                    'asset_id' => $model->id,
                    'asset_type' => $assetType,
                    'collection_name' => 'thumbnail',
                ]);
            }

            if (!empty($model->deleted_image_ids)) {
                $ids = $model->deleted_image_ids;
                if (!is_array($ids)) {
                    $ids = array_filter(array_map('intval', explode(',', $ids)));
                }
                if (!empty($ids)) {
                    $galleryCollections = [];
                    foreach ($this->attributes as $attr => $folder) {
                        if ($attr !== 'thumbnail' && !in_array($attr, $galleryCollections)) {
                            $galleryCollections[] = $attr;
                        }
                    }
                    if (in_array('images', $galleryCollections) || in_array('image', $galleryCollections)) {
                        if (!in_array('images', $galleryCollections)) $galleryCollections[] = 'images';
                        if (!in_array('image', $galleryCollections)) $galleryCollections[] = 'image';
                    }
                    if (empty($galleryCollections)) {
                        $galleryCollections = ['image', 'images'];
                    }

                    Asset::deleteAll([
                        'and',
                        [
                            'asset_id' => $model->id,
                            'asset_type' => $assetType,
                        ],
                        ['in', 'collection_name', $galleryCollections],
                        [
                            'or',
                            ['in', 'id', $ids],
                            ['in', 'file_id', $ids]
                        ],
                    ]);
                }
            }
        }

        if (!empty($this->attributes)) {
            try {
                Yii::$app->uploader->processUploads($model, $this->attributes);
            } catch (\Throwable $e) {
                Yii::error('UploadAssetBehavior: ' . $e->getMessage(), __METHOD__);
                throw $e;
            }
        }
    }
}
