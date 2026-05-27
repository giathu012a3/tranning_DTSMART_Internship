<?php

namespace app\controllers;

use Yii;
use app\models\Cart;
use app\models\CartDetail;
use app\models\forms\CartForm;
use app\models\response\CartResponse;

class CartController extends BaseApiController
{

    public function actionIndex()
    {
        try {
            $userId = Yii::$app->user->id;
            $cart = Cart::findOne(['user_id' => $userId]);
            if (!$cart) {
               return[
                'status'=>false,
                'data' => [
                    'items'=>[],
                    'total_price'=>0,
                    'total_items'=>0
                ]
               ];
            }

            return [
                'status' => true,
                'data' => $this->getCartResponse($cart),
                'message' => 'Cart retrieved successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving cart: ' . $e->getMessage(),
            ];
        }
    }

    public function actionCreate()
    {
        try {
            $form = new CartForm();
            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                $cart = $form->getCart();
                return [
                    'status' => true,
                    'data' => $this->getCartResponse($cart),
                    'message' => 'Item added to cart successfully',
                ];
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Invalid data.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        try {
            $userId = Yii::$app->user->id;
            $cartDetail = CartDetail::findOne($id);

            if (!$cartDetail || !$cartDetail->cart || $cartDetail->cart->user_id !== $userId) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Cart item not found or unauthorized.',
                ];
            }

            $form = new CartForm($cartDetail);
            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                $cart = $form->getCart();
                return [
                    'status' => true,
                    'data' => $this->getCartResponse($cart),
                    'message' => 'Cart updated successfully',
                ];
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Invalid data.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    public function actionDelete($id)
    {
        try {
            $userId = Yii::$app->user->id;
            $cartDetail = CartDetail::findOne($id);

            if (!$cartDetail || !$cartDetail->cart || $cartDetail->cart->user_id !== $userId) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Cart item not found or unauthorized.',
                ];
            }

            $cart = $cartDetail->cart;

            if ($cartDetail->delete()) {
                $cart->touch('updated_at');

                return [
                    'status' => true,
                    'data' => $this->getCartResponse($cart),
                    'message' => 'Item removed from cart successfully',
                ];
            }

            return [
                'status' => false,
                'data' => null,
                'message' => 'Failed to remove item from cart.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    public function actionClear()
    {
        try {
            $userId = Yii::$app->user->id;
            $cart = Cart::findOne(['user_id' => $userId]);

            if (!$cart) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Cart not found.',
                ];
            }

            CartDetail::deleteAll(['cart_id' => $cart->id]);
            $cart->touch('updated_at');

            return [
                'status' => true,
                'data' => $this->getCartResponse($cart),
                'message' => 'Cart cleared successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper method to eager load cartDetails and their products to avoid N+1 queries.
     *
     * @param Cart $cart
     * @return CartResponse
     */
    private function getCartResponse(Cart $cart)
    {
        $cartWithRelations = Cart::find()
            ->byId($cart->id)
            ->withDetails()
            ->one();

        return CartResponse::fromModel($cartWithRelations);
    }
}
