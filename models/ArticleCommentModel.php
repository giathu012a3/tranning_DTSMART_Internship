<?php

namespace app\models;

class ArticleCommentModel extends ArticleComment
{

    public function fields()
    {
        return [
            'id',
            'article_id',
            'user_id',
            'content',
            'parent_id',
            'status',
            'created_at' => fn() => $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => fn() => $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null,
        ];
    }
}
