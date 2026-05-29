<?php

namespace app\models\forms;

use app\models\ProductModel;
use app\models\ProductTag;
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
            [
                ['price'],
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'type' => 'number',
                'message' => 'Price must be greater than 0.'
            ],
            [
                ['name'],
                'unique',
                'filter' => ['deleted_at' => null],
                'message' => 'This product name already exists.'
            ],
            [['tags'], 'filter', 'filter' => function ($tags) {
                if (!is_array($tags)) return [];
                return array_unique(array_filter(array_map('trim', $tags), function ($v) {
                    return $v !== '' && strlen($v) <= 255;
                }));
            }],
            [['deleted_image_ids'], 'safe'],
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
        if (!$this->isNewRecord) {
            $dirty = $this->getDirtyAttributes();

            $tagsUnchanged = true;
            if ($this->tags !== null) {
                $currentTags = $this->getCurrentTags();
                $tagsUnchanged = (count($this->tags) === count($currentTags) &&
                    !array_diff($this->tags, $currentTags) &&
                    !array_diff($currentTags, $this->tags));
            }

            $noNewThumbnail = empty(UploadedFile::getInstanceByName('thumbnail'));
            $noNewImages = empty(UploadedFile::getInstancesByName('images'));
            $noDeletions = empty($this->deleted_image_ids);

            unset($dirty['updated_at'], $dirty['created_at']);

            if (empty($dirty) && $tagsUnchanged && $noNewThumbnail && $noNewImages && $noDeletions) {
                $this->addError('name', 'No changes detected. Please modify at least one field to update.');
            }
        }
    }

    public function getCurrentTags()
    {
        if ($this->_currentTags === null) {
            $this->_currentTags = ProductTag::find()
                ->alias('pt')
                ->innerJoinWith('tag t')
                ->where(['pt.product_id' => $this->id])
                ->select('t.name')
                ->column();
        }
        return $this->_currentTags;
    }

}
