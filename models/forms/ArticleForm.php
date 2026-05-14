<?php

namespace app\models\forms;

use app\models\Asset;
use Yii;
use yii\base\Model;
use app\models\Article;
use app\models\User;
use app\models\Tag;
use app\models\Product;
use app\models\ArticleTag;
use app\models\ProductArticle;
use app\components\UploadComponent;
use yii\web\UploadedFile;

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

        if (!$article->isNewRecord) {
            $this->tags = $article->getTags()->select('name')->column();
            $this->product_ids = $article->getProducts()->select('id')->column();
        }

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['title', 'content', 'author_id'], 'required'],
            [['content'], 'string'],
            [['status', 'author_id'], 'integer'],
            [['title', 'excerpt'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['tags', 'product_ids', 'deleted_image_ids'], 'safe'],
        ];
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

            if (!$this->_article->save()) {
                $this->addErrors($this->_article->getErrors());
                return false;
            }

            if (!$this->_article->isNewRecord) {
                $newThumbnail = UploadedFile::getInstanceByName('thumbnail');
                if ($newThumbnail) {
                    Asset::deleteAll([
                        'asset_id' => $this->_article->id,
                        'asset_type' => 'article',
                        'collection_name' => 'thumbnail'
                    ]);
                }

                if (!empty($this->deleted_image_ids) && is_array($this->deleted_image_ids)) {
                    Asset::deleteAll([
                        'and',
                        [
                            'asset_id' => $this->_article->id,
                            'asset_type' => 'article',
                            'collection_name' => 'image'
                        ],
                        ['in', 'id', $this->deleted_image_ids]
                    ]);
                }
            }

            Yii::$app->uploader->processUploads($this->_article, [
                'thumbnail' => 'articles',
                'image' => 'article_gallery'
            ]);

            $this->syncTags();
            $this->syncProducts();

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function syncTags()
    {
        if ($this->tags === null)
            return;

        $targetTagIds = [];
        if (is_array($this->tags)) {
            foreach ($this->tags as $name) {
                if (!is_string($name))
                    continue;
                $name = trim($name);
                if (empty($name) || strlen($name) > 255)
                    continue;

                $tag = Tag::findOne(['name' => $name]);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->name = $name;
                    $tag->save();
                }
                $targetTagIds[] = $tag->id;
            }
        }
        $targetTagIds = array_unique($targetTagIds);

        $currentTagIds = $this->_article->getTags()->select('id')->column();

        $tagsToAdd = array_diff($targetTagIds, $currentTagIds);
        $tagsToRemove = array_diff($currentTagIds, $targetTagIds);

        if (!empty($tagsToRemove)) {
            ArticleTag::deleteAll(['article_id' => $this->_article->id, 'tag_id' => $tagsToRemove]);
        }

        if (!empty($tagsToAdd)) {
            foreach ($tagsToAdd as $tagId) {
                $articleTag = new ArticleTag();
                $articleTag->article_id = $this->_article->id;
                $articleTag->tag_id = $tagId;
                $articleTag->save(false);
            }
        }
    }

    private function syncProducts()
    {
        if ($this->product_ids === null)
            return;

        $targetProductIds = [];
        if (is_array($this->product_ids)) {
            foreach ($this->product_ids as $pId) {
                if (!is_numeric($pId))
                    continue;
                $product = Product::find()->active()->andWhere(['id' => $pId])->one();
                if ($product) {
                    $targetProductIds[] = $product->id;
                }
            }
        }
        $targetProductIds = array_unique($targetProductIds);

        $currentProductIds = $this->_article->getProducts()->select('id')->column();

        $productsToAdd = array_diff($targetProductIds, $currentProductIds);
        $productsToRemove = array_diff($currentProductIds, $targetProductIds);

        if (!empty($productsToRemove)) {
            ProductArticle::deleteAll(['article_id' => $this->_article->id, 'product_id' => $productsToRemove]);
        }

        if (!empty($productsToAdd)) {
            foreach ($productsToAdd as $pId) {
                $productArticle = new ProductArticle();
                $productArticle->article_id = $this->_article->id;
                $productArticle->product_id = $pId;
                $productArticle->save(false);
            }
        }
    }
}
