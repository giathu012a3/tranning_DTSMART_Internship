<?php

namespace app\models\response;

use app\models\ProductModel;
use app\models\FileModel;

class ProductResponse extends ProductModel
{
    public bool $detailMode = false;

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = [
            'id',
            'name',
            'price',
            'stock',
            'status',
            'category_id',
            'category_name' => function () {
                return $this->category->name ?? 'N/A';
            },
            'tags' => function () {
                return array_map(fn($tag) => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ], $this->tags);
            },
            'created_at' => function () {
                return $this->created_at ? date('Y-m-d H:i:s', $this->created_at) : null;
            },
            'updated_at' => function () {
                return $this->updated_at ? date('Y-m-d H:i:s', $this->updated_at) : null;
            },
            'linked_items' => function () {
                return [
                    'files_count'    => count($this->assets),
                    'articles_count' => $this->articles_count ?? count($this->articles),
                ];
            },
        ];

        if ($this->detailMode) {
            $fields = array_merge($fields, $this->extraFields());
        } else {
            $fields['thumbnail_url'] = function () {
                foreach ($this->assets as $asset) {
                    if ($asset->collection_name === 'thumbnail' && $asset->file) {
                        return FileModel::buildUrl($asset->file->file_path);
                    }
                }
                return null;
            };
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return [
            'description' => 'description',
            'attachments' => function () {
                return array_map(fn($asset) => [
                    'id'              => $asset->id,
                    'file_id'         => $asset->file->id,
                    'file_name'       => $asset->file->file_name,
                    'file_path'       => $asset->file->file_path,
                    'file_size'       => $asset->file->file_size,
                    'collection_name' => $asset->collection_name,
                    'file_type'       => $asset->file->file_type,
                ], array_filter($this->assets, fn($asset) => $asset->file !== null));
            },
            'articles' => function () {
                return array_map(fn($article) => [
                    'id'      => $article->id,
                    'title'   => $article->title,
                    'slug'    => $article->slug,
                    'excerpt' => $article->excerpt,
                ], $this->articles);
            },
        ];
    }
}
