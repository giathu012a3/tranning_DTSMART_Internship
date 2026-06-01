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

    public static function findOrCreate(string $name, ?string &$error = null): ?Tag
    {
        $tag = static::findOne(['name' => $name]);
        if ($tag) {
            return $tag;
        }

        $form = new static();
        $form->name = $name;

        if ($form->save()) {
            return $form;
        }

        $error = implode(', ', $form->getFirstErrors());
        return null;
    }

    public static function resolveIds(array $names, array &$errors = []): array
    {
        $ids = [];
        foreach ($names as $name) {
            $tagError = null;
            $tag = static::findOrCreate($name, $tagError);
            if ($tag) {
                $ids[] = $tag->id;
            } else {
                $errors[] = "Tag '{$name}' failed: " . ($tagError ?: 'Unknown error');
            }
        }
        return $ids;
    }
}
