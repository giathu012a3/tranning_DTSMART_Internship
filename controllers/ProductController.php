<?php

namespace app\controllers;

use app\models\forms\ProductForm;
use app\models\forms\TagForm;
use app\models\response\ProductResponse;
use Yii;
use app\models\ProductModel;
use app\models\TagModel;
use app\models\search\ProductSearch;

class ProductController extends BaseApiController
{
    public function actionIndex()
    {
        $searchModel  = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');
        $serialized   = $this->serializeData($dataProvider);
        $items        = array_map(fn($model) => ProductResponse::fromModel($model), $dataProvider->getModels());

        return [
            'status'     => true,
            'data'       => $items,
            'pagination' => $serialized['pagination'],
            'message'    => 'Products retrieved successfully',
        ];
    }

    public function actionView($id)
    {
        $product = ProductModel::find()->notDeleted()->byId($id)->one();

        if (!$product) {
            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Product not found',
            ];
        }

        return [
            'status'  => true,
            'data'    => $this->serializeData($this->loadProductResponse($product->id)),
            'message' => 'Product retrieved successfully',
        ];
    }

    public function actionCreate()
    {
        $form = new ProductForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            TagModel::syncForProduct($form->id, TagForm::resolveIds($form->tags ?? []));

            return [
                'status'  => true,
                'data'    => $this->serializeData($this->loadProductResponse($form->id)),
                'message' => 'Product created successfully',
            ];
        }

        return [
            'status'  => false,
            'data'    => $form->getErrors(),
            'message' => 'Validation failed: ' . json_encode($form->errors),
        ];
    }

    public function actionUpdate($id)
    {
        $form = ProductForm::find()->byId($id)->notDeleted()->one();

        if (!$form) {
            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Product not found',
            ];
        }

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            TagModel::syncForProduct($form->id, TagForm::resolveIds($form->tags ?? []));

            return [
                'status'  => true,
                'data'    => $this->serializeData($this->loadProductResponse($form->id)),
                'message' => 'Product updated successfully',
            ];
        }

        return [
            'status'  => false,
            'data'    => $form->getErrors(),
            'message' => 'Validation failed: ' . json_encode($form->errors),
        ];
    }

    public function actionDelete($id)
    {
        $product = ProductModel::find()->byId($id)->notDeleted()->one();

        if (!$product) {
            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Product not found',
            ];
        }

        if ($product->softDelete()) {
            return [
                'status'  => true,
                'data'    => null,
                'message' => 'Product moved to trash successfully',
            ];
        }

        return [
            'status'  => false,
            'data'    => null,
            'message' => 'Failed to delete product',
        ];
    }

    private function loadProductResponse(int $id): ?ProductResponse
    {
        $product = ProductModel::find()
            ->withAsset()
            ->withCategory()
            ->withTags()
            ->withArticles()
            ->byId($id)
            ->one();

        return $product ? ProductResponse::fromModel($product) : null;
    }
}
