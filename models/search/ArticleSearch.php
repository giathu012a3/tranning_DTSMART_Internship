<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ArticleModel;
use app\models\ArticleComment;

/**
 * ArticleSearch represents the model behind the search form of `app\models\ArticleModel`.
 */
class ArticleSearch extends ArticleModel
{
    public $keyword;
    public $author_name;
    public $tag_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'like_count', 'author_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['title', 'content', 'slug', 'excerpt', 'author_name', 'tag_name'], 'safe'],
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
        $query = ArticleModel::find()
            ->select([
                'articles.id',
                'articles.title',
                'articles.slug',
                'articles.excerpt',
                'articles.status',
                'articles.like_count',
                'articles.author_id',
                'articles.created_at',
                'articles.updated_at',
                'comment_count' => ArticleComment::find()
                    ->select('COUNT(*)')
                    ->where('article_id = articles.id')
            ])
            ->notDeleted()
            ->withAsset()
            ->withProducts()
            ->withAuthor()
            ->withTags();

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

        // Apply filters
        $query->andFilterWhere([
            'articles.id' => $this->id,
            'articles.like_count' => $this->like_count,
            'articles.author_id' => $this->author_id,
            'articles.status' => $this->status,
            'articles.created_at' => $this->created_at,
            'articles.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'articles.title', $this->title])
            ->andFilterWhere(['like', 'articles.content', $this->content])
            ->andFilterWhere(['like', 'articles.slug', $this->slug])
            ->andFilterWhere(['like', 'articles.excerpt', $this->excerpt])
            ->andFilterWhere(['like', 'articles.slug', $this->keyword]);

        // Dynamically join only when search filters are active to prevent N+1 issues and pagination duplicates
        if (!empty($this->author_name)) {
            $query->joinWith('author');
            $query->andFilterWhere(['like', 'users.username', $this->author_name]);
        }

        if (!empty($this->tag_name)) {
            $query->joinWith('tags');
            $query->andFilterWhere(['like', 'tags.name', $this->tag_name]);
            $query->groupBy('articles.id');
        }

        return $dataProvider;
    }
}
