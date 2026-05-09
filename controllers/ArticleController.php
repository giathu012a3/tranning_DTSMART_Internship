<?php

namespace app\controllers;

use app\models\Article;

class ArticleController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    function actionView($slug)
    {
        try {
            $article = Article::find()
                ->where(['slug' => $slug, 'status' => 1])
                ->with([
                    'assets' => function ($query) {
                        $query->andWhere(['assets.asset_type' => 'post']);
                    }
                ])
                ->with(['assets.file'])
                ->asArray()->one();


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
                    'article' => $article,
                    'now' => date('Y-m-d H:i:s'),
                ],
                'message' => 'Article retrieved successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving article: ' . $e->getMessage(),
            ];
        }
    }
}
