<?php

namespace app\controllers;

use app\models\forms\TagForm;
use app\models\TagModel;
use app\models\search\TagSearch;
use Yii;

class TagController extends BaseApiController
{
    public function actionIndex()
    {
        try {
            $searchModel = new TagSearch();
            return $searchModel->search(Yii::$app->request->queryParams, '');
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving tags: ' . $e->getMessage());
        }
    }

    public function actionView($id)
    {
        try {
            $tag = TagModel::findOne($id);

            if (!$tag) {
                return $this->responseError('Tag not found', null);
            }

            return $tag;
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving tag: ' . $e->getMessage());
        }
    }

    public function actionCreate()
    {
        $form = new TagForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            return $form;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors()
        );
    }

    public function actionUpdate($id)
    {
        $form = TagForm::findOne($id);

        if (!$form) {
            return $this->responseError('Tag not found', null);
        }

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            return $form;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors()
        );
    }

    public function actionDelete($id)
    {
        try {
            $tag = TagModel::findOne($id);

            if (!$tag) {
                return $this->responseError('Tag not found', null);
            }

            if ($tag->delete()) {
                return $this->responseSuccess(null, 'Tag deleted successfully');
            }

            return $this->responseError('Failed to delete tag');
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting tag: ' . $e->getMessage());
        }
    }
}
