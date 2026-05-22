<?php

namespace app\models\response;

use app\models\Order;

class OrderResponse extends Order
{
    public $items_count;

    public function fields()
    {
        $fields = [
            'id',
            'user_id',
            'full_name',
            'phone',
            'final_total',
            'status',
            'created_at',
        ];

        if ($this->isRelationPopulated('orderDetails')) {
            $fields[] = 'email';
            $fields[] = 'address';
            $fields[] = 'total';
            $fields[] = 'discount_amount';
            $fields[] = 'payment_method';

            $fields['orderDetails'] = function ($model) {
                $items = [];
                foreach ($model->orderDetails as $detail) {
                    $items[] = [
                        'product_id'   => $detail->product_id,
                        'product_name' => $detail->product ? $detail->product->name : 'Unknown',
                        'quantity'     => $detail->quantity,
                        'price'        => $detail->price,
                    ];
                }
                return $items;
            };

            $fields['couponUsage'] = function ($model) {
                if (!$model->couponUsage) {
                    return null;
                }
                return [
                    'coupon_code'      => $model->couponUsage->applied_code,
                    'discount_type'    => $model->couponUsage->applied_type,
                    'discount_value'   => $model->couponUsage->applied_value,
                    'max_discount'     => $model->couponUsage->applied_max_amount,
                ];
            };
        } else {
            $fields['items_count'] = function ($model) {
                return $model->items_count !== null 
                    ? (int) $model->items_count 
                    : ($model->isRelationPopulated('orderDetails') ? count($model->orderDetails) : (int) $model->getOrderDetails()->count());
            };
        }

        return $fields;
    }

    /**
     * Factory method to create OrderResponse from an Order model.
     *
     * @param Order $order
     * @return self
     */
    public static function fromModel(Order $order)
    {
        $response = new self();
        self::populateRecord($response, $order->attributes);

        if (isset($order->items_count)) {
            $response->items_count = $order->items_count;
        }

        if ($order->isRelationPopulated('orderDetails')) {
            $response->populateRelation('orderDetails', $order->orderDetails);
        }
        if ($order->isRelationPopulated('couponUsage')) {
            $response->populateRelation('couponUsage', $order->couponUsage);
        }

        return $response;
    }
}
