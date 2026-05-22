<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\auth\HttpBearerAuth;
use app\models\Cart;
use app\models\CartDetail;
use app\models\forms\CartForm;
use app\models\response\CartResponse;

class CartController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        try {
            $userId = Yii::$app->user->id;
            $cart = Cart::findOne(['user_id' => $userId]);
            if (!$cart) {
                $cart = new Cart();
                $cart->user_id = $userId;
                $cart->save(false);
            }

            $response = new CartResponse();
            CartResponse::populateRecord($response, $cart->attributes);
            $response->populateRelation('cartDetails', $cart->cartDetails);

            return [
                'status' => true,
                'data' => $response,
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
            $form->scenario = CartForm::SCENARIO_ADD;
            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                $cart = $form->getCart();
                $response = new CartResponse();
                CartResponse::populateRecord($response, $cart->attributes);
                $response->populateRelation('cartDetails', $cart->cartDetails);

                return [
                    'status' => true,
                    'data' => $response,
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
            $form->scenario = CartForm::SCENARIO_UPDATE;
            $data = Yii::$app->request->post();

            if ($form->load($data, '') && $form->save()) {
                $cart = $form->getCart();
                $response = new CartResponse();
                CartResponse::populateRecord($response, $cart->attributes);
                $response->populateRelation('cartDetails', $cart->cartDetails);

                return [
                    'status' => true,
                    'data' => $response,
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

                $response = new CartResponse();
                CartResponse::populateRecord($response, $cart->attributes);
                $response->populateRelation('cartDetails', $cart->cartDetails);

                return [
                    'status' => true,
                    'data' => $response,
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

            $response = new CartResponse();
            CartResponse::populateRecord($response, $cart->attributes);
            $response->populateRelation('cartDetails', []);

            return [
                'status' => true,
                'data' => $response,
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
}
