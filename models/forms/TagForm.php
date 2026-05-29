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

    public static function findOrCreate(string $name): ?Tag
    {
        $tag = static::findOne(['name' => $name]);
        if ($tag) {
            return $tag;
        }

        $form = new static();
        $form->name = $name;

        return $form->save() ? $form : null;
    }

    public static function resolveIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $tag = static::findOrCreate($name);
            if ($tag) {
                $ids[] = $tag->id;
            }
        }
        return $ids;
    }
}
