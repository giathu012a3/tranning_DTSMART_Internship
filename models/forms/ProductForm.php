<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Product;
use app\models\Tag;
use app\models\ProductTag;
use app\models\Category;
use app\models\Asset;
use yii\web\UploadedFile;

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
            [['price', 'category_id', 'status', 'stock'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
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

            $this->_product->name = $this->name;
            $this->_product->price = $this->price;
            $this->_product->category_id = $this->category_id;
            $this->_product->status = $this->status ?? 1;
            $this->_product->stock = $this->stock;
            $this->_product->description = $this->description;

            if (!$this->_product->save()) {
                $this->addErrors($this->_product->getErrors());
                return false;
            }

            if (!$this->_product->isNewRecord) {
                $newThumbnail = UploadedFile::getInstanceByName('thumbnail');
                if ($newThumbnail) {
                    Asset::deleteAll([
                        'asset_id' => $this->_product->id,
                        'asset_type' => 'product',
                        'collection_name' => 'thumbnail'
                    ]);
                }

                if (!empty($this->deleted_image_ids) && is_array($this->deleted_image_ids)) {
                    Asset::deleteAll([
                        'and',
                        [
                            'asset_id' => $this->_product->id,
                            'asset_type' => 'product',
                            'collection_name' => 'image'
                        ],
                        ['in', 'id', $this->deleted_image_ids]
                    ]);
                }
            }

            Yii::$app->uploader->processUploads($this->_product, [
                'thumbnail' => 'products',
                'images' => 'product_gallery'
            ]);

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
            foreach ($tagsToAdd as $tagId) {
                $productTag = new ProductTag();
                $productTag->product_id = $this->_product->id;
                $productTag->tag_id = $tagId;
                $productTag->save(false);
            }
        }
    }
}
