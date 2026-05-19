<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Product;
use app\models\Tag;
use app\models\ProductTag;
use app\models\Category;

class ProductForm extends Model
{
    private $_product;

    public $name;
    public $price;
    public $category_id;
    public $status;
    public $stock;
    public $description;

    public $tags;

    public $deleted_image_ids;

    public function __construct(?Product $product = null, $config = [])
    {
        if ($product === null) {
            $product = new Product();
        }
        $this->_product = $product;

        $this->name = $product->name;
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->status = $product->status;
        $this->stock = $product->stock;
        $this->description = $product->description;

        if (!$product->isNewRecord) {
            $this->tags = $product->getTags()->select('name')->column();
        }

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['name', 'price', 'category_id', 'stock'], 'required'],
            [['price', 'stock'], 'number'],
            [['category_id', 'status'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['tags', 'deleted_image_ids'], 'safe'],
        ];
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->_product->name        = $this->name;
            $this->_product->price       = $this->price;
            $this->_product->category_id = $this->category_id;
            $this->_product->status      = $this->status ?? 1;
            $this->_product->stock       = $this->stock;
            $this->_product->description = $this->description;

            $this->_product->scenario         = $this->_product->isNewRecord ? Product::SCENARIO_CREATE : Product::SCENARIO_UPDATE;
            $this->_product->deleted_image_ids = $this->deleted_image_ids;

            if (!$this->_product->save()) {
                $this->addErrors($this->_product->getErrors());
                return false;
            }

            $this->syncTags();

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    private function syncTags()
    {
        if ($this->tags === null) {
            return;
        }

        $tagNames = [];
        if (is_array($this->tags)) {
            foreach ($this->tags as $name) {
                if (!is_string($name)) continue;
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

        $currentTagIds = $this->_product->getTags()->select('id')->column();

        $tagsToAdd = array_diff($targetTagIds, $currentTagIds);
        $tagsToRemove = array_diff($currentTagIds, $targetTagIds);

        if (!empty($tagsToRemove)) {
            ProductTag::deleteAll([
                'product_id' => $this->_product->id,
                'tag_id' => $tagsToRemove
            ]);
        }

        if (!empty($tagsToAdd)) {
            $rows = [];
            $time = time();
            foreach ($tagsToAdd as $tagId) {
                $rows[] = [$this->_product->id, $tagId, $time, $time];
            }
            Yii::$app->db->createCommand()->batchInsert(ProductTag::tableName(), ['product_id', 'tag_id', 'created_at', 'updated_at'], $rows)->execute();
        }
    }
}
