<?php

namespace app\models\forms;

use app\models\ProductModel;
use app\models\TagModel;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class ProductForm extends ProductModel
{
    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $tags;
    public array $tagErrors = [];

    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            [['stock'], 'number', 'min' => 0],
            [['price'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'Price must be greater than 0.'],
            [['name'], 'unique', 'filter' => ['is_deleted' => 0], 'message' => 'This product name already exists.'],
            [['tags'], 'filter', 'filter' => function ($tags) {
                $arr = is_string($tags) ? explode(',', $tags) : (array)$tags;
                return array_values(array_unique(array_filter(array_map('trim', $arr))));
            }],
            [['deleted_image_ids'], 'safe'],
            [['thumbnail'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'skipOnEmpty' => true],
            [['images'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'maxFiles' => 20, 'skipOnEmpty' => true],
            [['name'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    private $_currentTags = null;

    public function afterFind()
    {
        parent::afterFind();
        $this->tags = $this->getCurrentTags();
    }

    public function getCurrentTags()
    {
        if ($this->_currentTags === null) {
            $tagModels = $this->isRelationPopulated('tags') ? $this->relatedRecords['tags'] : $this->getTags()->all();
            $this->_currentTags = ArrayHelper::getColumn($tagModels, 'name');
        }
        return $this->_currentTags;
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $this->category_id = (int)$this->category_id;
        $this->price = sprintf('%.2f', $this->price);
        $this->stock = sprintf('%.2f', $this->stock);

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['created_at']);

        $dbChanged = !empty($dirty);

        $tagsChanged = ($this->tags !== null) && (
            count($this->tags) !== count($this->getCurrentTags()) ||
            array_diff($this->tags, $this->getCurrentTags())
        );

        $hasChanges = $dbChanged
            || $tagsChanged
            || UploadedFile::getInstanceByName('thumbnail')
            || UploadedFile::getInstancesByName('images')
            || !empty($this->deleted_image_ids);

        if (!$hasChanges) {
            $this->addError('name', 'No changes detected. Please modify at least one field to update.');
        }
     }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->tags !== null) {
            try {
                $tagIds = TagModel::resolveIds($this->tags, $this->tagErrors);
                TagModel::syncForProduct($this->id, $tagIds, $insert);
            } catch (\Throwable $e) {
                $this->tagErrors[] = 'Lỗi hệ thống khi đồng bộ tag: ' . $e->getMessage();
            }
        }
    }
}
