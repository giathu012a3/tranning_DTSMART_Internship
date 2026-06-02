<?php

namespace app\models\forms;

use app\models\Tag;
use app\models\TagModel;

class TagForm extends TagModel
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                ['name'],
                'unique',
                'message' => 'This tag name already exists.',
            ],
        ]);
    }
}
