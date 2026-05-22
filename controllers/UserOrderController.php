<?php

namespace app\controllers;

use Yii;
use app\models\Order;
use app\models\forms\OrderForm;
use app\models\response\OrderResponse;
use app\models\search\OrderSearch;


class UserOrderController extends BaseApiController
{

    public function actionCreate()
    {
        try {
            $form = new OrderForm();
            $form->scenario = OrderForm::SCENARIO_CHECKOUT;
            $data = Yii::$app->request->post();

            if ($form->load($data, '')) {
                $form->user_id = Yii::$app->user->id;
                $order = $form->save();
                if ($order !== false) {
                    $updatedOrder = Order::find()
                        ->byId($order->id)
                        ->withDetails()
                        ->one();
                    $orderResponse = OrderResponse::fromModel($updatedOrder);
                    return [
                        'status' => true,
                        'data' => $orderResponse->toArray([], ['orderDetails']),
                        'message' => 'Order created successfully. Thank you for your purchase!'
                    ];
                }
            }

            return [
                'status' => false,
                'data' => $form->getErrors(),
                'message' => 'Failed to process your order. Please check your input.'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'An error occurred while creating order: ' . $th->getMessage() . ' at line ' . $th->getLine()
            ];
        }
    }

    public function actionIndex()
    {
        try {
            $searchModel = new OrderSearch();
            $queryParams = Yii::$app->request->queryParams;
            $queryParams['user_id'] = Yii::$app->user->id;
            $dataProvider = $searchModel->search($queryParams, '');

            $data = array_map(function ($model) {
                return OrderResponse::fromModel($model)->toArray([], ['orderDetails']);
            }, $dataProvider->getModels());

            return [
                'status' => true,
                'data' => [
                    'items' => $data,
                    'now' => date('d/m/Y'),
                ],
                'pagination' => [
                    'total_count'  => (int) $dataProvider->getTotalCount(),
                    'page_count'   => (int) $dataProvider->getPagination()->getPageCount(),
                    'current_page' => (int) $dataProvider->getPagination()->getPage() + 1,
                    'per_page'     => (int) $dataProvider->getPagination()->pageSize,
                ],
                'message' => 'Orders retrieved successfully'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error retrieving orders: ' . $th->getMessage()
            ];
        }
    }

    public function actionView($id)
    {
        try {
            $order = Order::find()
                ->byId($id)
                ->notDeleted()
                ->andWhere(['user_id' => Yii::$app->user->id])
                ->withDetails()
                ->withCoupon()
                ->one();

            if (!$order) {
                return [
                    'status'  => false,
                    'data'    => null,
                    'message' => 'Order not found.',
                ];
            }

            $response = OrderResponse::fromModel($order);

            return [
                'status'  => true,
                'data'    => $response->toArray([], ['orderDetails', 'couponUsage']),
                'message' => 'Order retrieved successfully.',
            ];
        } catch (\Throwable $th) {
            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Error retrieving order: ' . $th->getMessage(),
            ];
        }
    }
}
