<?php

namespace app\controllers;

use app\models\response\ProductResponse;
use Yii;
use app\models\Product;
use app\models\search\ProductSearch;


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
                    'totalCount'   => (int) $dataProvider->getTotalCount(),
                    'pageCount'    => (int) $dataProvider->getPagination()->getPageCount(),
                    'currentPage'  => (int) $dataProvider->getPagination()->getPage() + 1,
                    'pageSize'     => (int) $dataProvider->getPagination()->pageSize,
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
            $product = Product::findOne($id);
            if (!$product) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Product not found',
                ];
            }
            return [
                'status' => true,
                'data' => [
                    'product' => $product->attributes,
                    'now' => date('Y-m-d H:i:s'),
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
        try {
            $product = new Product();
            $product->load(\Yii::$app->request->post(), '');

            $product->created_at = time();
            $product->updated_at = time();
            if ($product->save()) {
                return [
                    'status' => true,
                    'data' => [
                        'product' => $product->attributes,
                        'now' => date('Y-m-d H:i:s'),
                    ],
                    'message' => 'Product created successfully',
                ];
            }
            return [
                'status' => false,
                'data' => null,
                'message' => 'Validation failed: ' . json_encode($product->errors),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ];
        }
    }

    public function actionFeatured()
    {
        try {
            $searchModel = new ProductSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

            $featuredProducts = $dataProvider->getModels();

            $responseData = array_map(function ($item) {
                $response = new ProductResponse();
                ProductResponse::populateRecord($response, $item->attributes);
                $response->populateRelation('category', $item->category);
                return $response;
            }, $featuredProducts);
            return [
                'status' => true,
                'data' => [
                    'products' => $responseData,
                    'now' => date('Y-m-d H:i:s'),
                ],
                'message' => 'Featured products retrieved successfully',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving featured products: ' . $th->getMessage(),
            ];
        }
    }
}
