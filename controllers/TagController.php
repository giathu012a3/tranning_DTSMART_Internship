<?php

namespace app\controllers;

use app\models\forms\TagForm;
use app\models\response\TagResponse;
use app\models\search\TagSearch;
use Yii;
use yii\rest\Controller;

class TagController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');
        $serialized = $this->serializeData($dataProvider);

        return [
            'status' => true,
            'data' => $serialized['items'],
            'pagination' => $serialized['pagination'],
            'message' => 'Tags retrieved successfully',
        ];
    }

    public function actionView($id)
    {
        $tag = Tag::findOne($id);

        if (!$tag) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Tag not found',
            ];
        }

        return [
            'status' => true,
            'data' => TagResponse::fromModel($tag),
            'message' => 'Tag retrieved successfully',
        ];
    }

    public function actionCreate()
    {
        $form = new TagForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            return [
                'status' => true,
                'data' => TagResponse::fromModel($form),
                'message' => 'Tag created successfully',
            ];
        }

        return [
            'status' => false,
            'data' => $form->getErrors(),
            'message' => 'Validation failed: ' . json_encode($form->errors),
        ];
    }

    public function actionUpdate($id)
    {
        $form = TagForm::find()->andWhere(['id' => $id])->one();

        if (!$form) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Tag not found',
            ];
        }

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post(), '') && $form->save()) {
            return [
                'status' => true,
                'data' => TagResponse::fromModel($form),
                'message' => 'Tag updated successfully',
            ];
        }

        return [
            'status' => false,
            'data' => $form->getErrors(),
            'message' => 'Validation failed: ' . json_encode($form->errors),
        ];
    }

    public function actionDelete($id)
    {
        $tag = Tag::findOne($id);

        if (!$tag) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Tag not found',
            ];
        }

        if ($tag->delete()) {
            return [
                'status' => true,
                'data' => null,
                'message' => 'Tag deleted successfully',
            ];
        }

        return [
            'status' => false,
            'data' => null,
            'message' => 'Failed to delete tag',
        ];
    }
}
