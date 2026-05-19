<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Article;
use app\models\User;
use app\models\Tag;
use app\models\Product;
use app\models\ArticleTag;
use app\models\ProductArticle;

/**
 * ArticleForm gánh vác việc xác thực dữ liệu và logic nghiệp vụ cho Bài viết
 */
class ArticleForm extends Model
{
    private $_article;

    public $title;
    public $content;
    public $excerpt;
    public $status;
    public $author_id;

    public $tags;
    public $product_ids;
    public $deleted_image_ids;

    public function __construct(?Article $article = null, $config = [])
    {
        if ($article === null) {
            $article = new Article();
        }
        $this->_article = $article;

        $this->title = $article->title;
        $this->content = $article->content;
        $this->excerpt = $article->excerpt;
        $this->status = $article->status;
        $this->author_id = $article->author_id;

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['title', 'content', 'author_id'], 'required'],
            [['content'], 'string'],
            [['status', 'author_id'], 'integer'],
            [['title', 'excerpt'], 'string', 'max' => 255],
            [['tags', 'product_ids', 'deleted_image_ids'], 'safe'],
            [['title'], 'validateAnyChange', 'skipOnEmpty' => false],
        ];
    }

    public function validateAnyChange($attribute, $params)
    {
        if (!$this->_article->isNewRecord) {
            $titleUnchanged = ($this->title === $this->_article->title);
            $contentUnchanged = ($this->content === $this->_article->content);
            $excerptUnchanged = ($this->excerpt === $this->_article->excerpt);
            $statusUnchanged = ((int)$this->status === (int)$this->_article->status);
            $authorUnchanged = ((int)$this->author_id === (int)$this->_article->author_id);

            if ($this->tags === null) {
                $tagsUnchanged = true;
            } else {
                $targetTags = [];
                if (is_array($this->tags)) {
                    foreach ($this->tags as $name) {
                        if (is_string($name)) {
                            $name = trim($name);
                            if (!empty($name) && strlen($name) <= 255) {
                                $targetTags[] = $name;
                            }
                        }
                    }
                }
                $targetTags = array_unique($targetTags);
                $currentTags = \app\models\ArticleTag::find()
                    ->alias('at')
                    ->innerJoinWith('tag t')
                    ->where(['at.article_id' => $this->_article->id])
                    ->select('t.name')
                    ->column();
                $tagsUnchanged = (count($targetTags) === count($currentTags) && !array_diff($targetTags, $currentTags) && !array_diff($currentTags, $targetTags));
            }

            if ($this->product_ids === null) {
                $productsUnchanged = true;
            } else {
                $targetProducts = [];
                if (is_array($this->product_ids)) {
                    $targetProducts = array_unique(array_filter(array_map('intval', $this->product_ids)));
                }
                $currentProducts = \app\models\ProductArticle::find()->where(['article_id' => $this->_article->id])->select('product_id')->column();
                $productsUnchanged = (count($targetProducts) === count($currentProducts) && !array_diff($targetProducts, $currentProducts) && !array_diff($currentProducts, $targetProducts));
            }

            $noNewThumbnail = empty(\yii\web\UploadedFile::getInstanceByName('thumbnail'));
            $noNewImages = empty(\yii\web\UploadedFile::getInstancesByName('image'));
            $noDeletions = empty($this->deleted_image_ids);

            if ($titleUnchanged && $contentUnchanged && $excerptUnchanged && $statusUnchanged && $authorUnchanged && $tagsUnchanged && $productsUnchanged && $noNewThumbnail && $noNewImages && $noDeletions) {
                $this->addError('title', 'No changes detected. Please modify at least one field to update.');
            }
        }
    }

    public function getArticle()
    {
        return $this->_article;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->_article->title = $this->title;
            $this->_article->content = $this->content;
            $this->_article->excerpt = $this->excerpt;
            $this->_article->status = $this->status ?? 1;
            $this->_article->author_id = $this->author_id;

            $isNewRecord = $this->_article->isNewRecord;
            $this->_article->scenario = $isNewRecord ? Article::SCENARIO_CREATE : Article::SCENARIO_UPDATE;
            $this->_article->deleted_image_ids = $this->deleted_image_ids;

            if (!$this->_article->save()) {
                $this->addErrors($this->_article->getErrors());
                return false;
            }

            $this->syncTags($isNewRecord);
            $this->syncProducts($isNewRecord);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function syncTags($isNewRecord = false)
    {
        if ($this->tags === null)
            return;

        $tagNames = [];
        if (is_array($this->tags)) {
            foreach ($this->tags as $name) {
                if (!is_string($name))
                    continue;
                $name = trim($name);
                if (!empty($name) && strlen($name) <= 255) {
                    $tagNames[] = $name;
                }
            }
        }
        $tagNames = array_unique($tagNames);
        $targetTagIds = [];

        if (!empty($tagNames)) {
            $existingTags = Tag::find()->where(['in', 'name', $tagNames])->indexBy('name')->all();
            foreach ($tagNames as $name) {
                if (isset($existingTags[$name])) {
                    $targetTagIds[] = $existingTags[$name]->id;
                } else {
                    $tag = new Tag();
                    $tag->name = $name;
                    if ($tag->save()) {
                        $targetTagIds[] = $tag->id;
                    }
                }
            }
        }

        $currentTagIds = $isNewRecord ? [] : \app\models\ArticleTag::find()->where(['article_id' => $this->_article->id])->select('tag_id')->column();
        $tagsToAdd = array_diff($targetTagIds, $currentTagIds);
        $tagsToRemove = array_diff($currentTagIds, $targetTagIds);

        if (!empty($tagsToRemove)) {
            ArticleTag::deleteAll(['article_id' => $this->_article->id, 'tag_id' => $tagsToRemove]);
        }

        if (!empty($tagsToAdd)) {
            $rows = [];
            $time = time();
            foreach ($tagsToAdd as $tagId) {
                $rows[] = [$this->_article->id, $tagId, $time, $time];
            }
            Yii::$app->db->createCommand()->batchInsert(ArticleTag::tableName(), ['article_id', 'tag_id', 'created_at', 'updated_at'], $rows)->execute();
        }
    }

    private function syncProducts($isNewRecord = false)
    {
        if ($this->product_ids === null)
            return;

        $targetProductIds = [];
        if (is_array($this->product_ids)) {
            $ids = array_filter(array_map('intval', $this->product_ids));
            if (!empty($ids)) {
                $targetProductIds = Product::find()->active()->andWhere(['in', 'id', $ids])->select('id')->column();
            }
        }
        $targetProductIds = array_unique($targetProductIds);

        $currentProductIds = $isNewRecord ? [] : \app\models\ProductArticle::find()->where(['article_id' => $this->_article->id])->select('product_id')->column();
        $productsToAdd = array_diff($targetProductIds, $currentProductIds);
        $productsToRemove = array_diff($currentProductIds, $targetProductIds);

        if (!empty($productsToRemove)) {
            ProductArticle::deleteAll(['article_id' => $this->_article->id, 'product_id' => $productsToRemove]);
        }

        if (!empty($productsToAdd)) {
            $rows = [];
            $time = time();
            foreach ($productsToAdd as $pId) {
                $rows[] = [$this->_article->id, $pId, $time, $time];
            }
            Yii::$app->db->createCommand()->batchInsert(ProductArticle::tableName(), ['article_id', 'product_id', 'created_at', 'updated_at'], $rows)->execute();
        }
    }
}
