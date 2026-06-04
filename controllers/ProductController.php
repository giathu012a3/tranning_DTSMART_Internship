<?php

namespace app\controllers;

use app\models\forms\ProductForm;
use Yii;
use app\models\ProductModel;
use app\models\TagModel;
use app\models\search\ProductSearch;

class ProductController extends BaseApiController
{
    public function actionIndex()
    {
        try {
            $searchModel  = new ProductSearch();
            return $searchModel->search(Yii::$app->request->queryParams, '');
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving products: ' . $e->getMessage(), null, 500);
        }
    }

    public function actionView($id)
    {
        try {
            $product = $this->loadProduct($id);

            if (!$product || $product->is_deleted) {
                return $this->responseError('Product not found', null, 404);
            }

            return $product;
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving product: ' . $e->getMessage(), null, 500);
        }
    }

    public function actionCreate()
    {
        $form = new ProductForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $product = $this->loadProduct($form->id);
            if (!empty($form->tagErrors) || $form->hasErrors()) {
                $warnings = $this->extractWarnings($form, ['tags' => $form->tagErrors]);
                return $this->responseWithWarnings(
                    $product,
                    'Product created successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $product;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors(),
            422
        );
    }

    public function actionUpdate($id)
    {
        $form = ProductForm::find()->byId($id)->notDeleted()->one();

        if (!$form) {
            return $this->responseError('Product not found', null, 404);
        }

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $product = $this->loadProduct($form->id);
            if (!empty($form->tagErrors) || $form->hasErrors()) {
                $warnings = $this->extractWarnings($form, ['tags' => $form->tagErrors]);
                return $this->responseWithWarnings(
                    $product,
                    'Product updated successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $product;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors(),
            422
        );
    }

    public function actionDelete($id)
    {
        try {
            $product = ProductModel::find()->byId($id)->notDeleted()->one();

            if (!$product) {
                return $this->responseError('Product not found', null, 404);
            }

            if ($product->softDelete()) {
                return $this->responseSuccess(null, 'Product moved to trash successfully');
            }

            return $this->responseError('Failed to delete product', null, 400);
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting product: ' . $e->getMessage(), null, 500);
        }
    }

    private function loadProduct(int $id): ?ProductModel
    {
        $product = ProductModel::find()
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
