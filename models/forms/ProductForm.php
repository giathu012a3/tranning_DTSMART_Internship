<?php

namespace app\models\forms;

use app\models\ProductModel;
use yii\web\UploadedFile;

class ProductForm extends ProductModel
{

    public $tags;

    private $_currentTags = null;

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
            [['name'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->tags = $this->getCurrentTags();
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
}
