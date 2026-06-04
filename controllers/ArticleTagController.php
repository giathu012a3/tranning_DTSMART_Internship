<?php

namespace app\controllers;

use app\models\ArticleTagModel;
use app\models\TagModel;
use app\models\ArticleModel;
use Yii;

class ArticleTagController extends BaseApiController
{
    /**
     * Link a tag to an article.
     * POST /api/article-tags
     *
     * @return array
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->getBodyParams();
        $articleId = $params['article_id'] ?? null;
        $tagId = $params['tag_id'] ?? null;
        $tagName = $params['tag_name'] ?? null;

        if (($tagId === null || $tagId === '') && !empty($tagName)) {
            $errors = [];
            $tagIds = TagModel::resolveIds([$tagName], $errors);
            if (!empty($errors)) {
                return $this->responseError('Failed to resolve or create tag: ' . implode(', ', $errors));
            }
            $tagId = !empty($tagIds) ? $tagIds[0] : null;
        }

        $model = new ArticleTagModel();
        $model->article_id = $articleId;
        $model->tag_id = $tagId;

        if (!$model->validate()) {
            return $this->responseError('Validation failed', $model->getErrors());
        }

        $articleExists = ArticleModel::find()->where(['id' => $model->article_id])->notDeleted()->exists();
        if (!$articleExists) {
            return $this->responseError('Article does not exist or has been deleted');
        }

        $tagExists = TagModel::find()->where(['id' => $model->tag_id])->exists();
        if (!$tagExists) {
            return $this->responseError('Tag does not exist');
        }

        $alreadyLinked = ArticleTagModel::find()->where([
            'article_id' => $model->article_id,
            'tag_id' => $model->tag_id
        ])->exists();

        if ($alreadyLinked) {
            return $this->responseError('Tag is already linked to this article');
        }

        if ($model->save(false)) {
            return $this->responseSuccess($model, 'Tag linked to article successfully');
        }

        return $this->responseError('Failed to save association record');
    }

    /**
     * Unlink a tag from an article.
     * DELETE /api/article-tags/{id}
     *
     * @param int $id The association ID
     * @return array
     */
    public function actionDelete($id)
    {
        try {
            $model = ArticleTagModel::findOne($id);
            if (!$model) {
                return $this->responseError('Association record not found', null);
            }

            if ($model->delete()) {
                return $this->responseSuccess(null, 'Tag unlinked from article successfully');
            }

            return $this->responseError('Failed to delete association record');
        } catch (\Throwable $e) {
            return $this->responseError('Error deleting association: ' . $e->getMessage());
        }
    }
}
