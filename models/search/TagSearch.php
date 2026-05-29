<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Tag;

class TagSearch extends Tag
{
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Tag::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
            'sort'       => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'     => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
