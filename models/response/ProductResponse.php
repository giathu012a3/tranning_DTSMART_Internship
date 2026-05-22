<?php

namespace app\models\response;

use app\models\Product;

class ProductResponse extends Product
{
    public $articles_count;

    public function fields()
    {
        $fields = [
            'id',
            'name',
            'status',
            'price',
            'stock',
            'category_id',
            'category_name' => function ($model) {
                return $model->category ? $model->category->name : 'N/A';
            },
            'created_at',
            'updated_at',
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
            'linked_items' => function ($model) {
                $filesCount = 0;
                if ($model->isRelationPopulated('assets')) {
                    $filesCount = count($model->assets);
                } else {
                    $filesCount = (int) $model->getAssets()->count();
                }

                $articlesCount = $model->articles_count !== null 
                    ? (int) $model->articles_count 
                    : ($model->isRelationPopulated('articles') ? count($model->articles) : (int) $model->getArticles()->count());

                return [
                    'files_count' => $filesCount,
                    'articles_count' => $articlesCount,
                ];
            },
        ];

        if ($this->description === null) {
            $fields['thumbnail_url'] = function ($model) {
                if ($model->isRelationPopulated('assets')) {
                    foreach ($model->assets as $asset) {
                        if ($asset->collection_name === 'thumbnail' && $asset->file) {
                            return \Yii::$app->request->hostInfo . '/' . $asset->file->file_path;
                        }
                    }
                }
                if ($model->thumbnail && $model->thumbnail->file) {
                    return \Yii::$app->request->hostInfo . '/' . $model->thumbnail->file->file_path;
                }
                return null;
            };
        } else {
            $fields['description'] = 'description';
            $fields['attachments'] = function ($model) {
                $data = [];
                if ($model->isRelationPopulated('assets')) {
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
                }
                return $data;
            };
            $fields['articles'] = function ($model) {
                $articles = [];
                if ($model->isRelationPopulated('articles')) {
                    foreach ($model->articles as $article) {
                        $articles[] = [
                            'id' => $article->id,
                            'title' => $article->title,
                            'slug' => $article->slug,
                            'excerpt' => $article->excerpt,
                        ];
                    }
                }
                return $articles;
            };
        }

        return $fields;
    }

    /**
     * Factory method to create ProductResponse from a Product model.
     *
     * @param Product $product
     * @return self
     */
    public static function fromModel(Product $product)
    {
        $response = new self();
        self::populateRecord($response, $product->attributes);

        if (isset($product->articles_count)) {
            $response->articles_count = $product->articles_count;
        }

        if ($product->isRelationPopulated('assets')) {
            $response->populateRelation('assets', $product->assets);
        } elseif ($product->isRelationPopulated('thumbnail')) {
            $response->populateRelation('assets', $product->thumbnail ? [$product->thumbnail] : []);
        }
        if ($product->isRelationPopulated('category')) {
            $response->populateRelation('category', $product->category);
        }
        if ($product->isRelationPopulated('tags')) {
            $response->populateRelation('tags', $product->tags);
        }
        if ($product->isRelationPopulated('articles')) {
            $response->populateRelation('articles', $product->articles);
        }

        return $response;
    }
}
