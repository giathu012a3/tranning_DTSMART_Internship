<?php

namespace app\models\forms;

use app\models\ProductModel;
use app\models\TagModel;
use yii\web\UploadedFile;

class ProductForm extends ProductModel
{
    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            [['stock'], 'number', 'min' => 0],
            [['price'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'Price must be greater than 0.'],
            [['name'], 'unique', 'filter' => ['deleted_at' => null], 'message' => 'This product name already exists.'],
            [['tags'], 'filter', 'filter' => function ($tags) {
                $arr = is_string($tags) ? explode(',', $tags) : (array)$tags;
                return array_values(array_unique(array_filter(array_map('trim', $arr))));
            }],
            [['deleted_image_ids', 'thumbnail', 'images'], 'safe'],
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
            if ($this->isNewRecord) {
                $this->_currentTags = [];
            } else {
                $this->_currentTags = $this->getTags()->select('name')->column();
            }
        }
        return $this->_currentTags;
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['created_at']);

        $tagsChanged = ($this->tags !== null) && (
            count($this->tags) !== count($this->getCurrentTags()) ||
            array_diff($this->tags, $this->getCurrentTags())
        );

        $hasChanges = !empty($dirty)
            || $tagsChanged
            || UploadedFile::getInstanceByName('thumbnail')
            || UploadedFile::getInstancesByName('images')
            || !empty($this->deleted_image_ids);

        if (!$hasChanges) {
            $this->addError('name', 'No changes detected. Please modify at least one field to update.');
        }
    }
}
