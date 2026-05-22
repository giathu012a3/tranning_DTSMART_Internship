<?php

namespace app\models\response;

use app\models\Article;
use Override;

class ArticleResponse extends Article
{
    public $comment_count;

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'slug',
            'status',
            'created_at',
            'updated_at',
            'author_id',
            'author_name' => function ($model) {
                return $model->author ? $model->author->username : 'N/A';
            },
            'stats' => function ($model) {
                $commentCount = $model->comment_count !== null
                    ? (int) $model->comment_count
                    : (int) $model->getArticleComments()->count();

                return [
                    'view_count' => 0,
                    'like_count' => (int) ($model->like_count ?? 0),
                    'comment_count' => $commentCount,
                ];
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
            'linked_items' => function ($model) {
                $filesCount = 0;
                if ($model->isRelationPopulated('assets')) {
                    $filesCount = count($model->assets);
                } else {
                    $filesCount = (int) $model->getAssets()->count();
                }

                $productsCount = 0;
                if ($model->isRelationPopulated('products')) {
                    $productsCount = count($model->products);
                } else {
                    $productsCount = (int) $model->getProducts()->count();
                }

                return [
                    'files_count' => $filesCount,
                    'products_count' => $productsCount,
                ];
            },
        ];

        if ($this->excerpt !== null) {
            $fields['excerpt'] = 'excerpt';
        }

        if ($this->content === null) {
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
            $fields['content'] = 'content';
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
            $fields['products'] = function ($model) {
                $products = [];
                if ($model->isRelationPopulated('products')) {
                    foreach ($model->products as $product) {
                        $products[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'status' => $product->status,
                            'category_id' => $product->category_id,
                        ];
                    }
                }
                return $products;
            };
        }

        return $fields;
    }

    /**
     * Factory method to create ArticleResponse from an Article model.
     *
     * @param Article $article
     * @return self
     */
    public static function fromModel(Article $article)
    {
        $response = new self();
        self::populateRecord($response, $article->attributes);

        if (isset($article->comment_count)) {
            $response->comment_count = $article->comment_count;
        }

        if ($article->isRelationPopulated('author')) {
            $response->populateRelation('author', $article->author);
        }
        if ($article->isRelationPopulated('assets')) {
            $response->populateRelation('assets', $article->assets);
        } elseif ($article->isRelationPopulated('thumbnail')) {
            $response->populateRelation('assets', $article->thumbnail ? [$article->thumbnail] : []);
        }
        if ($article->isRelationPopulated('tags')) {
            $response->populateRelation('tags', $article->tags);
        }
        if ($article->isRelationPopulated('products')) {
            $response->populateRelation('products', $article->products);
        }

        return $response;
    }
}
