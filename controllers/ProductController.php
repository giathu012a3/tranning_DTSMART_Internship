<?php

namespace app\controllers;

use app\models\Asset;
use app\models\forms\ProductForm;
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
                ->with(['tags', 'articles'])
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
            $reponeseData->populateRelation('assets', $product->assets);
            $reponeseData->populateRelation('category', $product->category);
            $reponeseData->populateRelation('tags', $product->tags);
            $reponeseData->populateRelation('articles', $product->articles);
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
        $form = new ProductForm();

        try {
            if ($form->load(Yii::$app->request->post(), '')) {
                if ($form->save()) {
                    $product = $form->getProduct();

                    $updatedProduct = Product::find()->withAsset()->with(['tags', 'articles'])->byId($product->id)->one();
                    $responseData = new ProductResponse();
                    ProductResponse::populateRecord($responseData, $updatedProduct->attributes);
                    $responseData->populateRelation('assets', $updatedProduct->assets);
                    $responseData->populateRelation('category', $updatedProduct->category);
                    $responseData->populateRelation('tags', $updatedProduct->tags);
                    $responseData->populateRelation('articles', $updatedProduct->articles);

                    return [
                        'status' => true,
                        'data' => [
                            'product' => $responseData,
                            'now' => date('Y-m-d H:i:s'),
                        ],
                        'message' => 'Product created successfully',
                    ];
                }
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Validation failed: ' . json_encode($form->errors),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        $product = Product::find()->byId($id)->one();

        if (!$product) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Product not found',
            ];
        }

        $form = new ProductForm($product);

        try {
            if ($form->load(Yii::$app->request->post(), '')) {
                if ($form->save()) {
                    $updatedProduct = Product::find()->withAsset()->with(['tags', 'articles'])->byId($id)->one();
                    $responseData = new ProductResponse();
                    ProductResponse::populateRecord($responseData, $updatedProduct->attributes);
                    $responseData->populateRelation('assets', $updatedProduct->assets);
                    $responseData->populateRelation('category', $updatedProduct->category);
                    $responseData->populateRelation('tags', $updatedProduct->tags);
                    $responseData->populateRelation('articles', $updatedProduct->articles);

                    return [
                        'status' => true,
                        'data' => [
                            'product' => $responseData,
                            'now' => date('Y-m-d H:i:s'),
                        ],
                        'message' => 'Product updated successfully',
                    ];
                }
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Validation failed: ' . json_encode($form->errors),
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error updating product: ' . $th->getMessage(),
            ];
        }
    }
    public function actionDelete($id)
    {
        $product = Product::find()->byId($id)->active()
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
            $product->status = 0;
            $product->deleted_at = time();

            if ($product->save(false)) {
                $transaction->commit();
                return [
                    'status' => true,
                    'data' => null,
                    'message' => 'Product moved to trash successfully',
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

}
