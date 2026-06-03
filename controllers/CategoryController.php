<?php

namespace app\controllers;

use app\models\CategoryModel;
use app\models\forms\CategoryForm;
use app\models\search\CategorySearch;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;



/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    /**
     * Lists all Category models.
     *
     * @return string
     */
    public function actionIndex()
    {
        try {
            $searchModel  = new CategorySearch();
            $dataProvider = $searchModel->search($this->request->queryParams, '');
            $serialized   = $this->serializeData($dataProvider);

            return [
                'status'     => true,
                'data'       => $serialized['items'],
                'pagination' => $serialized['pagination'],
                'message'    => 'Categories retrieved successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving categories: ' . $e->getMessage(),
            ];
        }
        ;
    }

    /**
     * Displays a single Category model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try {
            $category = CategoryModel::find()->byId($id)->notDeleted()->one();
            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Category not found or inactive',
                ];
            }
            return [
                'status' => true,
                'data' => $category,
                'message' => 'Category retrieved successfully',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving category: ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        try {
            $form = new CategoryForm();
            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                return [
                    'status' => true,
                    'data' => $form,
                    'message' => 'Category created successfully'
                ];
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Invalid data.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try {
            $form = CategoryForm::find()
                ->byId($id)
                ->notDeleted()
                ->one();

            if (!$form) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'This category is not found',
                ];
            }

            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                return [
                    'status' => true,
                    'data' => $form,
                    'message' => 'Category updated successfully',
                ];
            }
            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Invalid data.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $category = CategoryModel::find()
                ->byId($id)
                ->notDeleted()
                ->one();

            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => "This category is not found"
                ];
            }

            if ($category->softDelete()) {
                return [
                    'status' => true,
                    'data' => null,
                    'message' => "The category has been successfully moved to the trash!"
                ];
            }

            return [
                'status' => false,
                'data' => null,
                'message' => 'Failed to delete category.'
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $e->getMessage(),
            ];
        }
    }

}
