<?php

namespace app\models\forms;

use yii\base\Model;

class OrderItemForm extends Model
{
    public $product_id;
    public $quantity;

    public function rules()
    {
        return [
            [['product_id', 'quantity'], 'required'],
            [['product_id', 'quantity'], 'integer', 'min' => 1],
        ];
    }
}
