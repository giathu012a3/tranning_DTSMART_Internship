<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Product;
use app\models\Coupon;
use app\models\CouponUsage;
use app\models\MembershipLevel;
use app\models\User;
use app\models\UserAddress;

class OrderForm extends Model
{
    public $payment_method;
    public $coupon_code;
    public $address_id;
    public $items;
    private $_coupon = null;
    private $_total = 0;
    private $_final_total = 0;
    private $_discount_amount = 0;

    private $_products = [];

    public function rules()
    {
        return [
            [['payment_method', 'items'], 'required'],
            [['payment_method', 'coupon_code'], 'string', 'max' => 255],
            [['address_id'], 'integer'],
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

        $productIds = [];
        foreach ($this->items as $index => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                $this->addError($attribute, "Item at index {$index} is missing product_id or quantity.");
                continue;
            }
            $productIds[] = (int) $item['product_id'];
        }

        $this->_products = [];
        if (!empty($productIds)) {
            $this->_products = Product::find()
                ->notDeleted()
                ->andWhere(['id' => $productIds])
                ->indexBy('id')
                ->all();
        }

        $this->_total = 0;

        foreach ($this->items as $index => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                continue;
            }

            if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                $this->addError($attribute, "Quantity for Product ID {$item['product_id']} must be greater than 0.");
                continue;
            }

            $product = $this->_products[$item['product_id']] ?? null;
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

        $addressQuery = UserAddress::find()->where(['user_id' => $userId, 'status' => 1]);
        if ($this->address_id) {
            $addressQuery->andWhere(['id' => $this->address_id]);
        } else {
            $addressQuery->andWhere(['is_default' => 1]);
        }
        $userAddress = $addressQuery->one();

        if (!$userAddress) {
            $this->addError('address_id', 'No shipping address found. Please add a default address to your account first.');
            return false;
        }

        $user = User::findOne($userId);

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
        if ($user && $user->member_ship_id) {
            $membership = MembershipLevel::findOne($user->member_ship_id);
            if ($membership && $membership->status == 1) {
                $membershipLevelId = $membership->id;
                $membershipDiscountRate = $membership->discount_rate;
                $membershipDiscount = ($this->_total * $membership->discount_rate) / 100;
            }
        }

        $this->_discount_amount = $couponDiscount + $membershipDiscount;
        $this->_final_total = max(0, $this->_total - $this->_discount_amount);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $order = new Order();
            $order->user_id = $userId;
            $order->membership_level_id = $membershipLevelId;
            $order->full_name = $userAddress->full_name;
            $order->phone = $userAddress->phone;
            $order->address = $userAddress->address;
            $order->email = $user->email;
            $order->payment_method = $this->payment_method;
            $order->membership_discount_rate = $membershipDiscountRate;
            $order->total = $this->_total;
            $order->discount_amount = $this->_discount_amount;
            $order->final_total = $this->_final_total;

            if (!$order->save()) {
                $this->addErrors($order->getErrors());
                $transaction->rollBack();
                return false;
            }

            $orderDetailsRows = [];
            $now = time();

            foreach ($this->items as $item) {
                $product = $this->_products[$item['product_id']];

                $orderDetailsRows[] = [
                    $order->id,
                    $product->id,
                    $item['quantity'],
                    $product->price,
                    $now,
                    $now
                ];

                $affectedRows = Product::updateAllCounters(
                    ['stock' => -$item['quantity']],
                    ['and', ['id' => $product->id], ['>=', 'stock', $item['quantity']]]
                );

                if ($affectedRows === 0) {
                    $transaction->rollBack();
                    throw new \Exception("Sorry, someone just bought the last '{$product->name}' before you. Please try again.");
                }
            }

            Yii::$app->db->createCommand()->batchInsert(
                OrderDetail::tableName(),
                ['order_id', 'product_id', 'quantity', 'price', 'created_at', 'updated_at'],
                $orderDetailsRows
            )->execute();

            if ($this->_coupon) {
                $couponUsage = new CouponUsage();
                $couponUsage->coupon_id = $this->_coupon->id;
                $couponUsage->user_id = $userId;
                $couponUsage->order_id = $order->id;
                $couponUsage->applied_code = $this->_coupon->code;
                $couponUsage->applied_type = $this->_coupon->type;
                $couponUsage->applied_value = $this->_coupon->value;
                $couponUsage->applied_max_amount = $this->_coupon->max_amount ?? null;
                if (!$couponUsage->save()) {
                    $this->addErrors($couponUsage->getErrors());
                    $transaction->rollBack();
                    return false;
                }
            }

            if ($userId) {
                $pointsEarned = (int) floor($this->_final_total / 10000);
                if ($pointsEarned > 0) {
                    User::updateAllCounters(['total_points' => $pointsEarned], ['id' => $userId]);

                    // Auto-upgrade membership level based on new total points
                    $updatedUser = User::findOne($userId);
                    if ($updatedUser) {
                        $newLevel = MembershipLevel::find()
                            ->where(['status' => 1])
                            ->andWhere(['<=', 'points_required', $updatedUser->total_points])
                            ->orderBy(['points_required' => SORT_DESC])
                            ->one();

                        if ($newLevel && $newLevel->id !== $updatedUser->member_ship_id) {
                            $updatedUser->member_ship_id = $newLevel->id;
                            $updatedUser->save(false);
                        }
                    }
                }
            }

            $transaction->commit();
            return $order;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
