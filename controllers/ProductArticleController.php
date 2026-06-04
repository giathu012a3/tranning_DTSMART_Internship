<?php

namespace app\controllers;

use app\models\ProductArticleModel;
use app\models\ProductModel;
use app\models\ArticleModel;
use Yii;

class ProductArticleController extends BaseApiController
{
    /**
     * Create a link between a product and an article.
     * POST /api/product-articles
     *
     * @return array
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->getBodyParams();
        
        $model = new ProductArticleModel();
        $model->product_id = $params['product_id'] ?? null;
        $model->article_id = $params['article_id'] ?? null;

        if (!$model->validate()) {
            return $this->responseError('Validation failed', $model->getErrors(), 422);
        }

        $productExists = ProductModel::find()->where(['id' => $model->product_id])->notDeleted()->exists();
        if (!$productExists) {
            return $this->responseError('Product does not exist or has been deleted', null, 404);
        }

        $articleExists = ArticleModel::find()->where(['id' => $model->article_id])->notDeleted()->exists();
        if (!$articleExists) {
            return $this->responseError('Article does not exist or has been deleted', null, 404);
        }

        $alreadyLinked = ProductArticleModel::find()->where([
            'product_id' => $model->product_id,
            'article_id' => $model->article_id
        ])->exists();

        if ($alreadyLinked) {
            return $this->responseError('Product is already linked to this article', null, 400);
        }

        if ($model->save(false)) {
            return $this->responseSuccess($model, 'Product linked to article successfully');
        }

        return $this->responseError('Failed to save association record', null, 500);
    }

    /**
     * Remove a link between a product and an article.
     * DELETE /api/product-articles/{id}
     *
     * @param int $id The association ID
     * @return array
     */
    public function actionDelete($id)
    {
        try {
            $model = ProductArticleModel::findOne($id);
            if (!$model) {
                return $this->responseError('Association record not found', null, 404);
            }

            if ($model->delete()) {
                return $this->responseSuccess(null, 'Product unlinked from article successfully');
            }

            return $this->responseError('Failed to delete association record', null, 400);
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting association: ' . $e->getMessage(), null, 500);
        }
    }
}
