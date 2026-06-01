<?php

namespace app\controllers;

use app\models\forms\ProductForm;
use app\models\forms\TagForm;
use Yii;
use app\models\ProductModel;
use app\models\TagModel;
use app\models\search\ProductSearch;
use app\models\response\ProductResponse;

class ProductController extends BaseApiController
{
    public function actionIndex()
    {
        try {
            $searchModel  = new ProductSearch();
            return $searchModel->search(Yii::$app->request->queryParams, '');
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving products: ' . $e->getMessage());
        }
    }

    public function actionView($id)
    {
        try {
            $product = $this->loadProduct($id);

            if (!$product || $product->deleted_at !== null) {
                return $this->responseError('Product not found', null);
            }

            return $product;
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving product: ' . $e->getMessage());
        }
    }

    public function actionCreate()
    {
        try {
            $form = new ProductForm();

            if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
                $tagErrors = [];
                TagModel::syncForProduct($form->id, TagForm::resolveIds($form->tags ?? [], $tagErrors));

                $product = $this->loadProduct($form->id);
                if (!empty($tagErrors)) {
                    return $this->responseSuccess(
                        $product,
                        'Product created successfully, but some tags failed to create: ' . implode('; ', $tagErrors)
                    );
                }

                return $product;
            }

            return $this->responseError(
                'Validation failed: ' . json_encode($form->errors),
                $form->getErrors()
            );
        } catch (\Throwable $e) {
            return $this->responseError('Error creating product: ' . $e->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        try {
            $form = ProductForm::find()->byId($id)->notDeleted()->one();

            if (!$form) {
                return $this->responseError('Product not found', null);
            }

            if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
                $tagErrors = [];
                TagModel::syncForProduct($form->id, TagForm::resolveIds($form->tags ?? [], $tagErrors));

                $product = $this->loadProduct($form->id);
                if (!empty($tagErrors)) {
                    return $this->responseSuccess(
                        $product,
                        'Product updated successfully, but some tags failed to create: ' . implode('; ', $tagErrors)
                    );
                }

                return $product;
            }

            return $this->responseError(
                'Validation failed: ' . json_encode($form->errors),
                $form->getErrors()
            );
        } catch (\Throwable $e) {
            return $this->responseError('Error updating product: ' . $e->getMessage());
        }
    }

    public function actionDelete($id)
    {
        try {
            $product = ProductModel::find()->byId($id)->notDeleted()->one();

            if (!$product) {
                return $this->responseError('Product not found', null);
            }

            if ($product->softDelete()) {
                return $this->responseSuccess(null, 'Product moved to trash successfully');
            }

            return $this->responseError('Failed to delete product');
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting product: ' . $e->getMessage());
        }
    }

    private function loadProduct(int $id): ?ProductResponse
    {
        $product = ProductResponse::find()
            ->withAsset()
            ->withCategory()
            ->withTags()
            ->withArticles()
            ->byId($id)
            ->one();

        if ($product !== null) {
            $product->detailMode = true;
        }

        return $product;
    }
}
