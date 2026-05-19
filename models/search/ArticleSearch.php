<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Article;

/**
 * ArticleSearch represents the model behind the search form of `app\models\Article`.
 */
class ArticleSearch extends Article
{
    public $keyword;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'like_count', 'author_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['title', 'content', 'slug', 'excerpt'], 'safe'],
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
        $query = Article::find()
            ->select(['id', 'title', 'slug', 'excerpt', 'status', 'like_count', 'author_id', 'created_at', 'updated_at']) // Bỏ content
            ->notDeleted()
            ->with([
                'thumbnail', 
                'author', 
                'tags',
                'products' => function (\yii\db\ActiveQuery $q) {
                    $q->select(['products.id', 'products.name', 'products.price']);
                }
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
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
            'id' => $this->id,
            'like_count' => $this->like_count,
            'author_id' => $this->author_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'excerpt', $this->excerpt])
            ->andFilterWhere(['like','slug', $this->keyword]);

        return $dataProvider;
    }
}
