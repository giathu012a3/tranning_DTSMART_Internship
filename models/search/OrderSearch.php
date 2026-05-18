<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Order;

/**
 * OrderSearch represents the model behind the search form of `app\models\Order`.
 */
class OrderSearch extends Order
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'membership_level_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['full_name', 'email', 'phone', 'address', 'payment_method'], 'safe'],
            [['discount_amount', 'total', 'final_total', 'membership_discount_rate'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string $formName
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = '')
    {
        $query = Order::find()
            ->byUser(\Yii::$app->user->id)
            ->notDeleted()
            ->withDetails();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'membership_level_id' => $this->membership_level_id,
            'membership_discount_rate' => $this->membership_discount_rate,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'final_total' => $this->final_total,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'full_name', $this->full_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'payment_method', $this->payment_method]);

        return $dataProvider;
    }
}
