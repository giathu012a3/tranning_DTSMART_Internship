<?php

namespace app\models\forms;

use Yii;
use app\models\CategoryModel;

class CategoryForm extends CategoryModel
{
    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            [
                ['name'],
                'unique',
                'filter' => ['is_deleted' => 0],
                'message' => 'This category name already exists.'
            ],
            [['name'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    public function getCategory()
    {
        return $this;
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $this->status = (int)$this->status;

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['created_at']);

        $dbChanged = !empty($dirty);

        if (!$dbChanged) {
            $this->addError('name', 'No changes detected. Please modify at least one field to update.');
        }
    }
}
