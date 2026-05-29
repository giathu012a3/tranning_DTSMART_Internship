<?php

namespace app\controllers;

use app\models\Article;
use app\models\forms\ArticleForm;
use app\models\response\ArticleResponse;
use app\models\search\ArticleSearch;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{

    /**
     * Lists all Article models.
     *
     * @return string
     */
    public function actionIndex()
    {
        try {
            $searchModel = new ArticleSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

            $models = $dataProvider->getModels();
            $data = array_map(function ($item) {
                return ArticleResponse::fromModel($item);
            }, $models);

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
                'message' => 'Articles retrieved successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving articles: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Displays a single Article model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try {
            $article = Article::find()
                ->byId($id)
                ->withAsset()
                ->withTags()
                ->withProducts()
                ->withAuthor()
                ->notDeleted()
                ->one();

            if (!$article) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Article not found',
                ];
            }

            return [
                'status' => true,
                'data' => [
                    'article' => ArticleResponse::fromModel($article),
                    'now' => date('d/m/Y'),
                ],
                'message' => 'Article retrieved successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving article: ' . $e->getMessage(),
            ];
        }
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
                    return [
                        'status' => true,
                        'data' => [
                            'article' => $this->getArticleResponse($form->getArticle()),
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
        $article = Article::find()->byId($id)->notDeleted()->one();

        if (!$article) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Article not found',
            ];
        }

        $form = new ArticleForm($article);

        try {
            if (Yii::$app->request->isPost) {
                $form->load(Yii::$app->request->post(), '');
                if ($form->save()) {
                    return [
                        'status' => true,
                        'data' => [
                            'article' => $this->getArticleResponse($form->getArticle()),
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
        try {
            $article = Article::find()->byId($id)->notDeleted()->one();

            if (!$article) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Article not found',
                ];
            }

            if ($article->softDelete()) {
                return [
                    'status' => true,
                    'data' => null,
                    'message' => 'Article deleted successfully',
                ];
            }

            return [
                'status' => false,
                'data' => null,
                'message' => 'Failed to delete article',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error deleting article: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to get a fully eager-loaded ArticleResponse object.
     *
     * @param Article $article
     * @return ArticleResponse
     */
    private function getArticleResponse(Article $article): ArticleResponse
    {
        $updatedArticle = Article::find()
            ->withAsset()
            ->withTags()
            ->withProducts()
            ->withAuthor()
            ->byId($article->id)
            ->notDeleted()
            ->one();

        return ArticleResponse::fromModel($updatedArticle);
    }
}
