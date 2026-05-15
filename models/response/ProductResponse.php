<?php

namespace app\models\response;

use app\models\Product;

class ProductResponse extends Product
{
    public function fields()
    {
        return [
            'id',
            'name',
            'description',
            'status',
            'price',
            'stock',
            'category_id',
            'category_name' => function ($model) {
                return $model->category ? $model->category->name : 'N/A';
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
            },
            'tags' => function ($model) {
                $tags = [];
                foreach ($model->tags as $tag) {
                    $tags[] = [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ];
                }
                return $tags;
            },
        ];
    }
}
