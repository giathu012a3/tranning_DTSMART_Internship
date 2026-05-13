<?php

namespace app\controllers;

use app\models\Asset;
use app\models\response\ProductResponse;
use Yii;
use app\models\Product;
use app\models\search\ProductSearch;
use yii\web\UploadedFile;

class ProductController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;


    public function actionIndex()
    {
        try {
            $searchModel = new ProductSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

            $models = $dataProvider->getModels();
            $responseData = array_map(function ($item) {
                $response = new ProductResponse();
                ProductResponse::populateRecord($response, $item->attributes);
                $response->populateRelation('category', $item->category);
                return $response;
            }, $models);

            return [
                'status' => true,
                'data' => [
                    'products' => $responseData,
                    'now' => date('Y-m-d H:i:s'),
                ],
                'pagination' => [
                    'totalCount' => (int) $dataProvider->getTotalCount(),
                    'pageCount' => (int) $dataProvider->getPagination()->getPageCount(),
                    'currentPage' => (int) $dataProvider->getPagination()->getPage() + 1,
                    'pageSize' => (int) $dataProvider->getPagination()->pageSize,
                ],
                'message' => 'Product retrieved successfully',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving product: ' . $th->getMessage(),
            ];
        }
    }

    public function actionView($id)
    {
        try {
            $product = Product::find()->activeCategory()
                ->withAsset()
                ->byId($id)
                ->one();

            if (!$product) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Product not found',
                ];
            }

            $reponeseData = new ProductResponse();
            ProductResponse::populateRecord($reponeseData, $product->attributes);
            return [
                'status' => true,
                'data' => [
                    'product' => $reponeseData,
                    'now' => date('d/m/Y'),
                ],
                'message' => 'Product retrieved successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving product: ' . $e->getMessage(),
            ];
        }
    }

    public function actionCreate()
    {
        $product = new Product();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($product->load(Yii::$app->request->post(), '')) {
                if ($product->save()) {
                    $transaction->commit();
                    return [
                        'status' => true,
                        'data' => [
                            'product' => $product->attributes,
                            'now' => date('Y-m-d H:i:s'),
                            'file' => $_FILES
                        ],
                        'message' => 'Product created successfully',
                    ];
                }
            }
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => $product->getErrors(),
                'message' => 'Validation failed: ' . json_encode($product->errors),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        $product = Product::find()
            ->byId($id)
            ->one();

        if (!$product) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Product not found',
            ];
        }
        $transaction =  Yii::$app->db->beginTransaction();
        try {
            $newThumbnail = UploadedFile::getInstanceByName('thumbnail');
            if ($product->load(Yii::$app->request->post(), '')) {
                if ($newThumbnail) {
                    Asset::deleteAll([
                        'asset_id' => $product->id,
                        'asset_type' => 'product',
                        'collection_name' => 'thumbnail'
                    ]);
                }

                $deletedImageIds = Yii::$app->request->post('deleted_image_ids', []);
                if (!empty($deletedImageIds) && is_array($deletedImageIds)) {
                    Asset::deleteAll([
                        'and',
                        [
                            'asset_id' => $product->id,
                            'asset_type' => 'product',
                            'collection_name' => 'image'
                        ],
                        ['in', 'id', $deletedImageIds]
                    ]);
                }
            }

            if ($product->save()) {
                $transaction->commit();
                $updatedProduct = Product::find()->withAsset()->byId($id)->one();
                $reponeseData = new ProductResponse();
                ProductResponse::populateRecord($reponeseData, $updatedProduct->attributes);
                return [
                    'status' => true,
                    'data' => [
                        'product' => $reponeseData,
                        'now' => date('d/m/Y'),
                    ],
                    'message' => 'Product updated successfully',
                ];
            }
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => $product->getErrors(),
                'message' => 'Validation failed: ' . json_encode($product->errors),
            ];
        } catch (\Throwable $th) {
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error updating product: ' . $th->getMessage(),
            ];
        }
    }
    public function actionDelete($id)
    {
        $product = Product::find()->byId($id)
            ->one();

        if (!$product) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Product not found',
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($product->delete()) {
                $transaction->commit();
                return [
                    'status' => true,
                    'data' => null,
                    'message' => 'Product deleted successfully',
                ];
            }
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => null,
                'message' => 'Failed to delete product',
            ];
        } catch (\Throwable $th) {
            $transaction->rollBack();
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error delete product: ' . $th->getMessage(),
            ];
        }
    }
    // public function actionFeatured()
    // {
    //     try {
    //         $searchModel = new ProductSearch();
    //         $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

    //         $featuredProducts = $dataProvider->getModels();

    //         $responseData = array_map(function ($item) {
    //             $response = new ProductResponse();
    //             ProductResponse::populateRecord($response, $item->attributes);
    //             $response->populateRelation('category', $item->category);
    //             return $response;
    //         }, $featuredProducts);
    //         return [
    //             'status' => true,
    //             'data' => [
    //                 'products' => $responseData,
    //                 'now' => date('Y-m-d H:i:s'),

    //             ],
    //             'message' => 'Featured products retrieved successfully',
    //         ];
    //     } catch (\Throwable $th) {
    //         return [
    //             'status' => false,
    //             'data' => null,
    //             'message' => 'Error retrieving featured products: ' . $th->getMessage(),
    //         ];
    //     }
    // }
}
