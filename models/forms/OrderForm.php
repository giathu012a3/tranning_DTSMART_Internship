<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Product;
use app\models\Coupon;
use app\models\CouponUsage;

class OrderForm extends Model
{
    public $full_name;
    public $email;
    public $phone;
    public $address;
    public $payment_method;

    public $items;
    public $coupon_code;

    private $_coupon = null;
    private $_total = 0;
    private $_final_total = 0;
    private $_discount_amount = 0;

    public function rules()
    {
        return [
            [['full_name', 'email', 'phone', 'address', 'payment_method', 'items'], 'required'],
            [['email'], 'email'],
            [['full_name', 'phone', 'address', 'payment_method', 'coupon_code'], 'string', 'max' => 255],
            ['items', 'validateItems'],
            ['coupon_code', 'validateCoupon'],
        ];
    }

    public function validateItems($attribute, $params)
    {
        if (!is_array($this->items) || empty($this->items)) {
            $this->addError($attribute, 'Order items cannot be empty.');
            return;
        }

        $this->_total = 0;

        foreach ($this->items as $index => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                $this->addError($attribute, "Item at index {$index} is missing product_id or quantity.");
                continue;
            }

            if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                $this->addError($attribute, "Quantity for Product ID {$item['product_id']} must be greater than 0.");
                continue;
            }

            $product = Product::findOne($item['product_id']);
            if (!$product || $product->status != 1) {
                $this->addError($attribute, "Product ID {$item['product_id']} does not exist or is inactive.");
                continue;
            }

            if ($product->stock < $item['quantity']) {
                $this->addError($attribute, "Product '{$product->name}' does not have enough stock. Requested: {$item['quantity']}, Available: {$product->stock}.");
                continue;
            }

            $this->_total += ($product->price * $item['quantity']);
        }
    }

    public function validateCoupon($attribute, $params)
    {
        if (empty($this->coupon_code)) {
            return;
        }

        $coupon = Coupon::find()->where(['code' => $this->coupon_code, 'status' => 1])->one();

        if (!$coupon) {
            $this->addError($attribute, 'Invalid coupon code.');
            return;
        }

        $now = time();
        if ($now < $coupon->start_date || $now > $coupon->expiry_date) {
            $this->addError($attribute, 'This coupon is expired or not yet active.');
            return;
        }

        if ($coupon->min_purchase > 0 && $this->_total < $coupon->min_purchase) {
            $this->addError($attribute, "This coupon requires a minimum purchase of {$coupon->min_purchase}.");
            return;
        }

        if ($coupon->usage_limit > 0) {
            $usageCount = CouponUsage::find()->where(['coupon_id' => $coupon->id])->count();
            if ($usageCount >= $coupon->usage_limit) {
                $this->addError($attribute, 'This coupon has reached its usage limit.');
                return;
            }
        }

        $this->_coupon = $coupon;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $userId = Yii::$app->user->id;

        $couponDiscount = 0;
        if ($this->_coupon) {
            if ($this->_coupon->type === 'percent') {
                $couponDiscount = ($this->_total * $this->_coupon->value) / 100;
                if ($this->_coupon->max_amount > 0 && $couponDiscount > $this->_coupon->max_amount) {
                    $couponDiscount = $this->_coupon->max_amount;
                }
            } else {
                $couponDiscount = $this->_coupon->value;
            }
        }

        $membershipDiscount = 0;
        $membershipLevelId = null;
        $membershipDiscountRate = 0.00;
        $user = \app\models\User::findOne($userId);
        if ($user && $user->member_ship_id) {
            $membership = \app\models\MembershipLevel::findOne($user->member_ship_id);
            if ($membership && $membership->status == 1) {
                $membershipLevelId = $membership->id;
                $membershipDiscountRate = $membership->discount_rate;
                $membershipDiscount = ($this->_total * $membership->discount_rate) / 100;
            }
        }

        $this->_discount_amount = $couponDiscount + $membershipDiscount;

        $this->_final_total = $this->_total - $this->_discount_amount;
        if ($this->_final_total < 0) {
            $this->_final_total = 0;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $order = new Order();
            $order->user_id = $userId;
            $order->membership_level_id = $membershipLevelId;
            $order->membership_discount_rate = $membershipDiscountRate;
            $order->full_name = $this->full_name;
            $order->email = $this->email;
            $order->phone = $this->phone;
            $order->address = $this->address;
            $order->payment_method = $this->payment_method;
            $order->total = $this->_total;
            $order->discount_amount = $this->_discount_amount;
            $order->final_total = $this->_final_total;
            $order->status = 1;

            if (!$order->save()) {
                $this->addErrors($order->getErrors());
                $transaction->rollBack();
                return false;
            }

            foreach ($this->items as $item) {
                $product = Product::findOne($item['product_id']);

                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $product->id;
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->price = $product->price;

                if (!$orderDetail->save()) {
                    $this->addErrors($orderDetail->getErrors());
                    $transaction->rollBack();
                    return false;
                }

                $affectedRows = Product::updateAllCounters(
                    ['stock' => -$item['quantity']],
                    ['and', ['id' => $product->id], ['>=', 'stock', $item['quantity']]]
                );

                if ($affectedRows === 0) {
                    $transaction->rollBack();
                    throw new \Exception("Sorry, someone just bought the last '{$product->name}' before you. Please try again.");
                }
            }

            if ($this->_coupon) {
                $couponUsage = new CouponUsage();
                $couponUsage->coupon_id = $this->_coupon->id;
                $couponUsage->user_id = $this->user_id;
                $couponUsage->order_id = $order->id;
                if (!$couponUsage->save()) {
                    $this->addErrors($couponUsage->getErrors());
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();
            return $order;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
