<?php

namespace app\controllers;

use app\models\CategoryModel;
use app\models\forms\CategoryForm;
use app\models\search\CategorySearch;
use Yii;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends BaseApiController
{
    /**
     * Lists all Category models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        try {
            $searchModel  = new CategorySearch();
            return $searchModel->search(Yii::$app->request->queryParams, '');
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving categories: ' . $e->getMessage());
        }
    }

    /**
     * Displays a single Category model.
     * @param int $id ID
     * @return mixed
     */
    public function actionView($id)
    {
        try {
            $category = $this->loadCategory($id);

            if (!$category || $category->is_deleted) {
                return $this->responseError('Category not found', null);
            }

            return $category;
        } catch (\Throwable $e) {
            return $this->responseError('Error retrieving category: ' . $e->getMessage());
        }
    }

    /**
     * Creates a new Category model.
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new CategoryForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $category = $this->loadCategory($form->id);
            if ($form->hasErrors()) {
                $warnings = $this->extractWarnings($form);
                return $this->responseWithWarnings(
                    $category,
                    'Category created successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $category;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors()
        );
    }

    /**
     * Updates an existing Category model.
     * @param int $id ID
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $form = CategoryForm::find()->byId($id)->notDeleted()->one();

        if (!$form) {
            return $this->responseError('Category not found', null);
        }

        if ($form->load(Yii::$app->request->post(), '') && $form->save()) {
            $category = $this->loadCategory($form->id);
            if ($form->hasErrors()) {
                $warnings = $this->extractWarnings($form);
                return $this->responseWithWarnings(
                    $category,
                    'Category updated successfully, but some parts had warnings.',
                    $warnings
                );
            }

            return $category;
        }

        return $this->responseError(
            'Validation failed: ' . json_encode($form->errors),
            $form->getErrors()
        );
    }

    /**
     * Deletes an existing Category model.
     * @param int $id ID
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $category = CategoryModel::find()->byId($id)->notDeleted()->one();

            if (!$category) {
                return $this->responseError('Category not found', null);
            }

            if ($category->softDelete()) {
                return $this->responseSuccess(null, 'Category moved to trash successfully');
            }

            return $this->responseError('Failed to delete category');
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting category: ' . $e->getMessage());
        }
    }

    private function loadCategory(int $id): ?CategoryModel
    {
        return CategoryModel::find()
            ->withProducts()
            ->byId($id)
            ->one();
    }
}
