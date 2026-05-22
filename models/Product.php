<?php

namespace app\models;

use app\behaviors\UploadAssetBehavior;
use app\models\ProductsQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property float $stock
 * @property int $status
 * @property string|null $description
 * @property int $category_id
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 */
class Product extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public $deleted_image_ids;
    public $thumbnail;
    public $images;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'deleted_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['name', 'price', 'stock', 'category_id'], 'required'],
            [['stock'], 'number', 'min' => 0],
            [['price'], 'number'],
            [
                ['price'],
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'type' => 'number',
                'message' => 'Price must be greater than 0.'
            ],
            [['status', 'category_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [
                ['name'],
                'unique',
                'filter' => ['deleted_at' => null],
                'message' => 'This product name already exists.'
            ],
            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id']
            ],
            [
                ['thumbnail'],
                'required',
                'on' => self::SCENARIO_CREATE,
                'message' => 'Product thumbnail is required.'
            ],
            [
                ['thumbnail'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'jpg, jpeg, png, webp',
                'maxSize' => 5242880
            ],
            [
                ['images'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'jpg, jpeg, png, webp',
                'maxSize' => 5242880,
                'maxFiles' => 10
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'name',
            'price',
            'stock',
            'category_id',
            'status',
            'description',
            'thumbnail',
            'images'
        ];
        $scenarios[self::SCENARIO_UPDATE] = [
            'name',
            'price',
            'stock',
            'category_id',
            'status',
            'description',
            'thumbnail',
            'images',
            'deleted_image_ids'
        ];
        return $scenarios;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => UploadAssetBehavior::class,
                'attributes' => [
                    'thumbnail' => 'products',
                    'images'    => 'product_gallery',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'price' => 'Price',
            'stock' => 'Stock',
            'status' => 'Status',
            'description' => 'Description',
            'category_id' => 'Category ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getProductArticles()
    {
        return $this->hasMany(ProductArticle::class, ['product_id' => 'id']);
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])->via('productArticles')->active();
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getCartDetails()
    {
        return $this->hasMany(CartDetail::class, ['product_id' => 'id']);
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetail::class, ['product_id' => 'id']);
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(Asset::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'product']);
    }

    public function getThumbnail()
    {
        return $this->hasOne(Asset::class, ['asset_id' => 'id'])
            ->onCondition(['asset_type' => 'product', 'collection_name' => 'thumbnail'])
            ->with('file');
    }

    /**
     *  @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['id' => 'file_id'])->via('assets');
    }
    public static function find()
    {
        return new ProductsQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTags()
    {
        return $this->hasMany(ProductTag::class, ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('productTags');
    }

    public function softDelete()
    {
        $this->deleted_at = time();
        return $this->save(false);
    }
}
