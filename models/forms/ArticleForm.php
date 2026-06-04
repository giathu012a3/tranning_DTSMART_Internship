<?php

namespace app\models\forms;

use app\models\ArticleModel;
use yii\web\UploadedFile;


class ArticleForm extends ArticleModel
{
    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            [['tags'], 'filter', 'filter' => function ($tags) {
                $arr = is_string($tags) ? explode(',', $tags) : (array)$tags;
                return array_values(array_unique(array_filter(array_map('trim', $arr))));
            }],
            [['thumbnail'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'skipOnEmpty' => true],
            [['images'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'maxFiles' => 20, 'skipOnEmpty' => true],
            [['title'], 'validateHasChanges', 'skipOnEmpty' => false],
            [['deleted_image_ids'], 'safe'],
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

        $this->author_id = (int)$this->author_id;
        $this->status = (int)$this->status;

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
            $this->addError('title', 'No changes detected. Please modify at least one field to update.');
        }
    }
}
