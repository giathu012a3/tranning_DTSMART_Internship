<?php

namespace app\models\response;

use app\models\Order;

class OrderResponse extends Order
{
    public function fields()
    {
        return [
            'id',
            'user_id',
            'full_name',
            'email',
            'phone',
            'address',
            'total',
            'discount_amount',
            'final_total',
            'payment_method',
            'status',
            'created_at',
        ];
    }

    public function extraFields()
    {
        return [
            'orderDetails' => function ($model) {
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
            },
            'couponUsage' => function ($model) {
                if (!$model->couponUsage) {
                    return null;
                }
                return [
                    'coupon_code'      => $model->couponUsage->applied_code,
                    'discount_type'    => $model->couponUsage->applied_type,
                    'discount_value'   => $model->couponUsage->applied_value,
                    'max_discount'     => $model->couponUsage->applied_max_amount,
                ];
            },
        ];
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

        if ($order->isRelationPopulated('orderDetails')) {
            $response->populateRelation('orderDetails', $order->orderDetails);
        }
        if ($order->isRelationPopulated('couponUsage')) {
            $response->populateRelation('couponUsage', $order->couponUsage);
        }

        return $response;
    }
}
