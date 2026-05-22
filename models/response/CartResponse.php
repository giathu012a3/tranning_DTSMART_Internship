<?php

namespace app\models\response;

use app\models\Cart;

class CartResponse extends Cart
{
    public function fields()
    {
        return [
            'id',
            'user_id',
            'created_at',
            'updated_at',
            'items' => function ($model) {
                $items = [];
                foreach ($model->cartDetails as $detail) {
                    $product = $detail->product;
                    $items[] = [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'product_name' => $product ? $product->name : 'Unknown',
                        'product_price' => $product ? (float)$product->price : 0.0,
                        'quantity' => $detail->quantity,
                        'subtotal' => $product ? (float)($product->price * $detail->quantity) : 0.0,
                        'stock' => $product ? $product->stock : 0,
                    ];
                }
                return $items;
            },
            'total_items' => function ($model) {
                $total = 0;
                foreach ($model->cartDetails as $detail) {
                    $total += $detail->quantity;
                }
                return $total;
            },
            'total_price' => function ($model) {
                $totalPrice = 0.0;
                foreach ($model->cartDetails as $detail) {
                    $product = $detail->product;
                    if ($product) {
                        $totalPrice += ($product->price * $detail->quantity);
                    }
                }
                return (float)$totalPrice;
            },
        ];
    }

    /**
     * Factory method to create CartResponse from a Cart model.
     *
     * @param Cart $cart
     * @return self
     */
    public static function fromModel(Cart $cart)
    {
        $response = new self();
        self::populateRecord($response, $cart->attributes);

        if ($cart->isRelationPopulated('cartDetails')) {
            $response->populateRelation('cartDetails', $cart->cartDetails);
        }

        return $response;
    }
}
