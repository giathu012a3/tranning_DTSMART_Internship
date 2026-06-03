<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductModel;
use app\models\ProductArticle;

class ProductSearch extends ProductModel
{
    public $category;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'category_id', 'created_at', 'updated_at', 'deleted_at', 'is_deleted'], 'integer'],
            [['name', 'description', 'category'], 'safe'],
            [['price', 'stock'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = ProductModel::find()
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.status',
                'products.category_id',
                'products.created_at',
                'products.updated_at',
                'articles_count' => ProductArticle::find()
                    ->select('COUNT(*)')
                    ->where('product_id = products.id')
            ])
            ->notDeleted()
            ->leftJoin('categories', 'categories.id = products.category_id AND categories.is_deleted = 0')
            ->withAsset()
            ->withTags()
            ->withCategory();


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
                'validatePage' => true,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'products.id' => $this->id,
            'products.price' => $this->price,
            'products.stock' => $this->stock,
            'products.status' => $this->status,
            'products.category_id' => $this->category_id,
            'products.created_at' => $this->created_at,
            'products.updated_at' => $this->updated_at,
            'products.is_deleted' => $this->is_deleted,
        ]);

        $query->andFilterWhere(['like', 'products.name', $this->name]);
        $query->andFilterWhere(['like', 'categories.name', $this->category]);
        // ->andFilterWhere(['like', 'category.id', $this->category->id]);

        return $dataProvider;
    }
}
