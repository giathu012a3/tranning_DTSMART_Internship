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

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['name', 'price', 'category_id', 'stock'], 'required'],
            [['stock'], 'number', 'min' => 0],
            [['price'], 'number'],
            [['price'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'Price must be greater than 0.'],
            [['category_id', 'status'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['tags', 'deleted_image_ids'], 'safe'],
            [['name'], 'validateAnyChange', 'skipOnEmpty' => false],
        ];
    }

    public function validateAnyChange($attribute, $params)
    {
        if (!$this->_product->isNewRecord) {
            $nameUnchanged = ($this->name === $this->_product->name);
            $priceUnchanged = ((float)$this->price === (float)$this->_product->price);
            $categoryUnchanged = ((int)$this->category_id === (int)$this->_product->category_id);
            $statusUnchanged = ((int)$this->status === (int)$this->_product->status);
            $stockUnchanged = ((float)$this->stock === (float)$this->_product->stock);
            $descriptionUnchanged = ($this->description === $this->_product->description);

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
                $currentTags = \app\models\ProductTag::find()
                    ->alias('pt')
                    ->innerJoinWith('tag t')
                    ->where(['pt.product_id' => $this->_product->id])
                    ->select('t.name')
                    ->column();
                $tagsUnchanged = (count($targetTags) === count($currentTags) &&
                    !array_diff($targetTags, $currentTags) &&
                    !array_diff($currentTags, $targetTags));
            }

            $noNewThumbnail = empty(\yii\web\UploadedFile::getInstanceByName('thumbnail'));
            $noNewImages = empty(\yii\web\UploadedFile::getInstancesByName('images')) && empty(\yii\web\UploadedFile::getInstanceByName('images'));
            $noDeletions = empty($this->deleted_image_ids);

            if (
                $nameUnchanged && $priceUnchanged &&
                $categoryUnchanged && $statusUnchanged &&
                $stockUnchanged && $descriptionUnchanged &&
                $tagsUnchanged && $noNewThumbnail &&
                $noNewImages && $noDeletions
            ) {
                $this->addError('name', 'No changes detected. Please modify at least one field to update.');
            }
        }
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

            $isNewRecord = $this->_product->isNewRecord;
            $this->_product->scenario         = $isNewRecord ? Product::SCENARIO_CREATE : Product::SCENARIO_UPDATE;
            $this->_product->deleted_image_ids = $this->deleted_image_ids;

            if (!$this->_product->save()) {
                $this->addErrors($this->_product->getErrors());
                return false;
            }

            $this->syncTags($isNewRecord);

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    private function syncTags($isNewRecord = false)
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

        $currentTagIds = $isNewRecord ? [] : \app\models\ProductTag::find()
            ->where(['product_id' => $this->_product->id])
            ->select('tag_id')->column();

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
            Yii::$app->db->createCommand()
                ->batchInsert(ProductTag::tableName(), ['product_id', 'tag_id', 'created_at', 'updated_at'], $rows)
                ->execute();
        }
    }
}
