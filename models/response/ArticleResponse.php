<?php
namespace app\models\response;

use app\models\Article;
use Override;

class ArticleResponse extends Article{

    public function fields()
    {
        return [
            'id',
            'title',
            'slug',
            'excerpt',
            'content',
            'status',
            'like_count',
            'author_id',
            'created_at',
            'updated_at',

            'author_name' => function ($model) {
                return $model->author ? $model->author->username : 'N/A';
            },

            'attachments' => function ($model) {
                $data = [];
                foreach ($model->assets as $asset) {
                    if ($asset->file) {
                        $data[] = [
                            'id' => $asset->id,
                            'file_id' => $asset->file->id,
                            'file_name' => $asset->file->file_name,
                            'file_path' => $asset->file->file_path,
                            'file_size' => $asset->file->file_size,
                            'collection_name' => $asset->collection_name,
                            'file_type'  => $asset->file->file_type
                        ];
                    }
                }
                return $data;
            }
        ];
    }
}