<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Cart;
use app\models\CartDetail;
use app\models\Product;

class CartForm extends Model
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_UPDATE = 'update';

    public $product_id;
    public $quantity;

    private $_cartDetail;
    private $_cart;

    public function __construct(?CartDetail $cartDetail = null, $config = [])
    {
        if ($cartDetail !== null) {
            $this->_cartDetail = $cartDetail;
            $this->quantity = $cartDetail->quantity;
            $this->product_id = $cartDetail->product_id;
        }
        parent::__construct($config);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ADD => ['product_id', 'quantity'],
            self::SCENARIO_UPDATE => ['quantity'],
        ];
    }

    public function rules()
    {
        return [
            [['product_id'], 'required', 'on' => self::SCENARIO_ADD],
            [['quantity'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['product_id', 'quantity'], 'integer'],
            [['quantity'], 'default', 'value' => 1, 'on' => self::SCENARIO_ADD],
            [['quantity'], 'integer', 'min' => 1],
            [['product_id'], 'validateProduct', 'on' => self::SCENARIO_ADD],
            [['quantity'], 'validateStock', 'on' => self::SCENARIO_UPDATE],
        ];
    }

    public function validateProduct($attribute, $params)
    {
        $product = Product::find()->byId($this->product_id)->active()->notDeleted()->one();
        if (!$product) {
            $this->addError($attribute, 'Product does not exist or is inactive.');
            return;
        }

        $userId = Yii::$app->user->id;
        $cart = Cart::findOne(['user_id' => $userId]);
        $existingQty = 0;
        if ($cart) {
            $existingItem = CartDetail::findOne(['cart_id' => $cart->id, 'product_id' => $this->product_id]);
            if ($existingItem) {
                $existingQty = $existingItem->quantity;
            }
        }

        $totalNewQty = $existingQty + $this->quantity;
        if ($totalNewQty > $product->stock) {
            $this->addError('quantity', "Not enough stock. Available: {$product->stock}, currently in cart: {$existingQty}.");
        }
    }

    public function validateStock($attribute, $params)
    {
        if (!$this->_cartDetail) {
            $this->addError($attribute, 'Invalid cart item.');
            return;
        }

        $product = $this->_cartDetail->product;
        if (!$product || $product->status != 1 || $product->deleted_at !== null) {
            $this->addError($attribute, 'Product is no longer available.');
            return;
        }

        if ($this->quantity > $product->stock) {
            $this->addError($attribute, "Not enough stock. Available: {$product->stock}.");
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($this->scenario === self::SCENARIO_ADD) {
                $userId = Yii::$app->user->id;
                $cart = Cart::findOne(['user_id' => $userId]);
                if (!$cart) {
                    $cart = new Cart();
                    $cart->user_id = $userId;
                    if (!$cart->save()) {
                        $this->addErrors($cart->getErrors());
                        $transaction->rollBack();
                        return false;
                    }
                } else {
                    $cart->touch('updated_at');
                }

                $this->_cart = $cart;

                $detail = CartDetail::findOne(['cart_id' => $cart->id, 'product_id' => $this->product_id]);
                if ($detail) {
                    $detail->quantity += $this->quantity;
                } else {
                    $detail = new CartDetail();
                    $detail->cart_id = $cart->id;
                    $detail->product_id = $this->product_id;
                    $detail->quantity = $this->quantity;
                }

                if (!$detail->save()) {
                    $this->addErrors($detail->getErrors());
                    $transaction->rollBack();
                    return false;
                }
            } elseif ($this->scenario === self::SCENARIO_UPDATE) {
                $this->_cartDetail->quantity = $this->quantity;
                if (!$this->_cartDetail->save()) {
                    $this->addErrors($this->_cartDetail->getErrors());
                    $transaction->rollBack();
                    return false;
                }

                $cart = $this->_cartDetail->cart;
                $cart->touch('updated_at');
                $this->_cart = $cart;
            }

            $transaction->commit();
            return $this->_cart;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getCart()
    {
        return $this->_cart;
    }
}
