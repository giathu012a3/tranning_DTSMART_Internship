<?php

namespace app\controllers;

use app\models\Category;
use app\models\forms\CategoryForm;

use app\models\response\CategoryResponse;
use app\models\search\CategorySearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    public $enableCsrfValidation = false;
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

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
                    'dataProvider' => $data,
                    'now' => date('Y-m-d H:i:s'),
                ],
                'pagination' => [
                    'totalCount'   => (int) $dataProvider->getTotalCount(),
                    'pageCount'    => (int) $dataProvider->getPagination()->getPageCount(),
                    'currentPage'  => (int) $dataProvider->getPagination()->getPage() + 1,
                    'pageSize'     => (int) $dataProvider->getPagination()->pageSize,
                ],
                'message' => 'Categories retrieved successfully',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving categories: ' . $th->getMessage(),
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
            $category = Category::findOne($id);
            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Category not found',
                ];
            }
            if ($category->status != 1) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Category is inactive',
                ];
            }
            return [
                'status' => true,
                'data' => [
                    'category' => $category->attributes,
                    'now' => date('d/m/Y'),
                ],
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
                    'data' => $form->getCategory()->attributes,
                    'message' => 'Category created successfully'
                ];
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Invalid data.',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $th->getMessage(),
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
            $category = Category::findOne($id);

            if (!$category) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'This category is not found',
                ];
            }

            $form = new CategoryForm($category);
            $data = Yii::$app->request->getBodyParams();

            if ($form->load($data, '')) {
                if ($form->save()) {
                    return [
                        'status' => true,
                        'data' => [
                            'data' => $form->getCategory()->attributes,
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
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $th->getMessage(),
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
            $model = Category::find()->where(['id' => $id, 'status' => 1]);

            if (!$model) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => "This category is not found"
                ];
            }

            $model->status = 0;

            if ($model->save(false)) {
                return [
                    'status' => true,
                    'data' => [
                        'data' => $model->attributes,
                        'now' => date('d/m/Y')
                    ],
                    'message' => "The category has been successfully moved to the trash!"
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error category: ' . $th->getMessage(),
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
