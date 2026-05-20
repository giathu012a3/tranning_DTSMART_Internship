<?php

namespace app\controllers;

use app\models\Category;
use app\models\forms\CategoryForm;
use app\models\response\CategoryResponse;
use app\models\search\CategorySearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Lists all Category models.
     *
     * @return string
     */
    public function actionIndex()
    {
        try {
            $searchModel = new CategorySearch();
            $dataProvider = $searchModel->search($this->request->queryParams, '');

            $model = $dataProvider->getModels();
            $data = array_map(function ($item) {
                $response = new CategoryResponse();
                CategoryResponse::populateRecord($response, $item->attributes);
                return $response;
            }, $model);
            return [
                'status' => true,
                'data' => [
                    'items' => $data,
                    'now' => date('d/m/Y'),
                ],
                'pagination' => [
                    'total_count' => (int) $dataProvider->getTotalCount(),
                    'page_count' => (int) $dataProvider->getPagination()->getPageCount(),
                    'current_page' => (int) $dataProvider->getPagination()->getPage() + 1,
                    'per_page' => (int) $dataProvider->getPagination()->pageSize,
                ],
                'message' => 'Categories retrieved successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving categories: ' . $e->getMessage(),
            ];
        };
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
            $category = Category::find()->byId($id)->notDeleted()->one();
            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Category not found or inactive',
                ];
            }
            $response = new CategoryResponse();
            CategoryResponse::populateRecord($response, $category->attributes);
            return [
                'status' => true,
                'data' => $response,
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
                $response = new CategoryResponse();
                CategoryResponse::populateRecord($response, $form->getCategory()->attributes);
                return [
                    'status' => true,
                    'data' => $response,
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
            $category = Category::find()->byId($id)->notDeleted()->one();

            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'This category is not found',
                ];
            }

            $form = new CategoryForm($category);
            $data = Yii::$app->request->post();

            if ($form->load($data, '')) {
                if ($form->save()) {
                    $response = new CategoryResponse();
                    CategoryResponse::populateRecord($response, $form->getCategory()->attributes);
                    return [
                        'status' => true,
                        'data' => [
                            'data' => $response,
                            'now' => date('d/m/Y')
                        ],
                        'message' => 'Category updated successfully'
                    ];
                }
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
            $category = Category::find()->byId($id)->notDeleted()->one();

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

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
