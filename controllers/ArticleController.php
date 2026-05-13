<?php

namespace app\controllers;

use app\models\Article;
use app\models\response\ArticleResponse;
use app\models\search\ArticleSearch;
use Attribute;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use function PHPUnit\Framework\once;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{
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
     * Lists all Article models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '');

        $models = $dataProvider->getModels();
        $dataProvider->query->andWhere(['status' => 1]);
        $responseData = array_map(function ($item) {
            $response = new ArticleResponse();
            ArticleResponse::populateRecord($response, $item->attributes);
            return $response;
        }, $models);
        return [
            'status' => true,
            'data' => [
                'articles' => $responseData,
                'pagination' => [
                    'total_count' => $dataProvider->getTotalCount(),
                    'current_page' => $dataProvider->pagination->getPage() + 1,
                    'per_page' => $dataProvider->pagination->getPageSize(),
                ]
            ],
            'message' => 'Lấy danh sách bài viết thành công',
        ];
    }

    /**
     * Displays a single Article model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $article = Article::find()->byID($id)->active()->one();
        // return  $article;
        if (!$article) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Product not found',
            ];
        }
        $responseData = new ArticleResponse();
        ArticleResponse::populateRecord($responseData, $article->attributes);
        return [
            'status' => true,
            'data' => [
                'product' => $responseData,
                'now' => date('d/m/Y'),
            ],
            'message' => 'Article retrieved successfully',
        ];
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Article();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Article the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
