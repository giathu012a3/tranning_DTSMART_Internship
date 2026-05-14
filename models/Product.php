<?php

namespace app\models;

use app\behaviors\UploadBehavior;
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
            [['price', 'stock'], 'number'],
            [['status', 'category_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
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

}
