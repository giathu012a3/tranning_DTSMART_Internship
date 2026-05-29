<?php

namespace app\models\response;

use app\models\Tag;

class TagResponse extends Tag
{
    public function fields()
    {
        return [
            'id',
            'name',
            'status',
            'created_at',
        ];
    }

    public static function fromModel(Tag $tag): self
    {
        $response = new self();
        self::populateRecord($response, $tag->attributes);

        return $response;
    }
}
