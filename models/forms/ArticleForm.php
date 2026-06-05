<?php

namespace app\models\forms;

use app\models\ArticleModel;
use app\models\ProductModel;
use app\models\TagModel;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;


class ArticleForm extends ArticleModel
{
    public $deleted_image_ids;
    public $thumbnail;
    public $images;
    public $tags;
    public array $tagErrors = [];
    public $product_ids;

    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            [['tags'], 'filter', 'filter' => function ($tags) {
                $arr = is_string($tags) ? explode(',', $tags) : (array)$tags;
                return array_values(array_unique(array_filter(array_map('trim', $arr))));
            }],
            [['product_ids'], 'filter', 'filter' => function ($product_ids) {
                $arr = is_string($product_ids) ? explode(',', $product_ids) : (array)$product_ids;
                return array_values(array_unique(array_filter(array_map('intval', $arr))));
            }],
            [['product_ids'], 'exist', 'targetClass' => ProductModel::class, 'targetAttribute' => 'id', 'allowArray' => true, 'message' => 'Một hoặc nhiều sản phẩm liên kết không hợp lệ.'],
            [['thumbnail'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'skipOnEmpty' => true],
            [['images'], 'image', 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 10 * 1024 * 1024, 'maxFiles' => 20, 'skipOnEmpty' => true],
            [['title'], 'validateHasChanges', 'skipOnEmpty' => false],
            [['deleted_image_ids'], 'safe'],
        ]);
    }

    private $_currentTags = null;
    private $_currentProducts = null;

    public function afterFind()
    {
        parent::afterFind();
        $this->tags = $this->getCurrentTags();
        $this->product_ids = $this->getCurrentProducts();
    }

    public function getCurrentTags()
    {
        if ($this->_currentTags === null) {
            $tagModels = $this->isRelationPopulated('tags') ? $this->relatedRecords['tags'] : $this->getTags()->all();
            $this->_currentTags = ArrayHelper::getColumn($tagModels, 'name');
        }
        return $this->_currentTags;
    }

    public function getCurrentProducts()
    {
        if ($this->_currentProducts === null) {
            $this->_currentProducts = array_map('intval', ArrayHelper::getColumn($this->products, 'id'));
        }
        return $this->_currentProducts;
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $this->author_id = (int)$this->author_id;
        $this->status = (int)$this->status;

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['created_at']);

        $dbChanged = !empty($dirty);

        $tagsChanged = ($this->tags !== null) && (
            count($this->tags) !== count($this->getCurrentTags()) ||
            array_diff($this->tags, $this->getCurrentTags())
        );

        $productsChanged = ($this->product_ids !== null) && (
            count($this->product_ids) !== count($this->getCurrentProducts()) ||
            array_diff($this->product_ids, $this->getCurrentProducts())
        );

        $hasChanges = $dbChanged
            || $tagsChanged
            || $productsChanged
            || UploadedFile::getInstanceByName('thumbnail')
            || UploadedFile::getInstancesByName('images')
            || !empty($this->deleted_image_ids);

        if (!$hasChanges) {
            $this->addError('title', 'No changes detected. Please modify at least one field to update.');
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->tags !== null) {
            try {
                $tagIds = TagModel::resolveIds($this->tags, $this->tagErrors);
                TagModel::syncForArticle($this->id, $tagIds, $insert);
            } catch (\Throwable $e) {
                $this->tagErrors[] = 'Lỗi hệ thống khi đồng bộ tag: ' . $e->getMessage();
            }
        }
    }
}
