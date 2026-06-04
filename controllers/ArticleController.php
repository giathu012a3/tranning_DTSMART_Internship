<?php

namespace app\controllers;

use app\models\forms\ArticleForm;
use app\models\ArticleModel;
use app\models\search\ArticleSearch;
use Yii;

class ArticleController extends BaseApiController
{
    public function actionIndex()
    {
        try {
            $searchModel = new ArticleSearch();
            return $searchModel->search(Yii::$app->request->queryParams, '');
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving articles: ' . $e->getMessage(), null, 500);
        }
    }

    public function actionView($id)
    {
        try {
            $article = $this->loadArticle($id);

            if (!$article) {
                return $this->responseError('Article not found', null, 404);
            }

            return $article;
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving article: ' . $e->getMessage(), null, 500);
        }
    }

    public function actionCreate()
    {
        $form = new ArticleForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $article = $this->loadArticle($form->id);
            if (!empty($form->tagErrors) || $form->hasErrors()) {
                $warnings = $this->extractWarnings($form, ['tags' => $form->tagErrors]);
                return $this->responseWithWarnings(
                    $article,
                    'Article created successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $article;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors(),
            422
        );
    }

    public function actionUpdate($id)
    {
        $form = ArticleForm::find()->byId($id)->notDeleted()->one();

        if (!$form) {
            return $this->responseError('Article not found', null, 404);
        }

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $article = $this->loadArticle($form->id);
            if (!empty($form->tagErrors) || $form->hasErrors()) {
                $warnings = $this->extractWarnings($form, ['tags' => $form->tagErrors]);
                return $this->responseWithWarnings(
                    $article,
                    'Article updated successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $article;
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
            $article = ArticleModel::find()->byId($id)->notDeleted()->one();

            if (!$article) {
                return $this->responseError('Article not found', null, 404);
            }

            if ($article->softDelete()) {
                return $this->responseSuccess(null, 'Article moved to trash successfully');
            }

            return $this->responseError('Failed to delete article', null, 400);
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting article: ' . $e->getMessage(), null, 500);
        }
    }

    private function loadArticle(int $id): ?ArticleModel
    {
        $article = ArticleModel::find()
            ->withAsset()
            ->withTags()
            ->withProducts()
            ->withAuthor()
            ->byId($id)
            ->notDeleted()
            ->one();

        if ($article !== null) {
            $article->detailMode = true;
        }

        return $article;
    }
}
