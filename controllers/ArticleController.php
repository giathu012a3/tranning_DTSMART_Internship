<?php

namespace app\controllers;

use app\models\Article;
use app\models\forms\ArticleForm;
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
        $article = Article::find()->byId($id)->withAsset()->with(['tags', 'products', 'author'])->active()->one();
        
        if (!$article) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Article not found',
            ];
        }
        $responseData = new ArticleResponse();
        ArticleResponse::populateRecord($responseData, $article->attributes);
        $responseData->populateRelation('assets', $article->assets);
        $responseData->populateRelation('author', $article->author);
        $responseData->populateRelation('tags', $article->tags);
        $responseData->populateRelation('products', $article->products);

        return [
            'status' => true,
            'data' => [
                'article' => $responseData,
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
        $form = new ArticleForm();
        
        try {
            if ($form->load(Yii::$app->request->post(), '')) {
                if ($form->save()) {
                    $article = $form->getArticle();
                    
                    $updatedArticle = Article::find()->withAsset()->with(['tags', 'products', 'author'])->byId($article->id)->one();
                    $responseData = new ArticleResponse();
                    ArticleResponse::populateRecord($responseData, $updatedArticle->attributes);
                    $responseData->populateRelation('assets', $updatedArticle->assets);
                    $responseData->populateRelation('author', $updatedArticle->author);
                    $responseData->populateRelation('tags', $updatedArticle->tags);
                    $responseData->populateRelation('products', $updatedArticle->products);

                    return [
                        'status' => true,
                        'data' => [
                            'article' => $responseData,
                            'now' => date('d/m/Y'),
                        ],
                        'message' => 'Article created successfully'
                    ];
                }
            }
            
            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Validation failed: ' . json_encode($form->errors),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error creating article: ' . $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        $article = Article::find()->byId($id)->one();

        if (!$article) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Article not found',
            ];
        }

        $form = new ArticleForm($article);

        try {
            if ($form->load(Yii::$app->request->post(), '')) {
                if ($form->save()) {
                    $updatedArticle = Article::find()->withAsset()->with(['tags', 'products', 'author'])->byId($id)->one();
                    $responseData = new ArticleResponse();
                    ArticleResponse::populateRecord($responseData, $updatedArticle->attributes);
                    $responseData->populateRelation('assets', $updatedArticle->assets);
                    $responseData->populateRelation('author', $updatedArticle->author);
                    $responseData->populateRelation('tags', $updatedArticle->tags);
                    $responseData->populateRelation('products', $updatedArticle->products);

                    return [
                        'status' => true,
                        'data' => [
                            'article' => $responseData,
                            'now' => date('d/m/Y'),
                        ],
                        'message' => 'Article updated successfully',
                    ];
                }
            }
            
            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Validation failed: ' . json_encode($form->errors),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error updating article: ' . $e->getMessage(),
            ];
        }
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
        $model = $this->findModel($id);
        $model->status = 0;
        $model->deleted_at = time();
        
        if ($model->save(false)) {
            return [
                'status' => true,
                'data' => null,
                'message' => 'Article deleted successfully!'
            ];
        }

        return [
            'status' => false,
            'data' => null,
            'message' => 'Failed to delete article'
        ];
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
