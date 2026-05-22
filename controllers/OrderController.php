<?php

namespace app\controllers;

use Yii;
use app\models\Order;
use app\models\forms\OrderForm;
use app\models\response\OrderResponse;
use app\models\search\OrderSearch;

class OrderController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['delete'];
        return $behaviors;
    }

    public function actionCreate()
    {
        try {
            $form = new OrderForm();
            $data = Yii::$app->request->post();

            if ($form->load($data, '')) {
                $order = $form->save();
                if ($order !== false) {
                    $updatedOrder = Order::find()
                        ->byId($order->id)
                        ->withDetails()
                        ->one();
                    $orderResponse = OrderResponse::fromModel($updatedOrder);
                    return [
                        'status' => true,
                        'data' => $orderResponse->toArray(),
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

    /**
     * Lists orders.
     */
    public function actionIndex()
    {
        try {
            $searchModel = new OrderSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

            $data = array_map(function ($model) {
                return OrderResponse::fromModel($model)->toArray();
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
                'data'    => $response->toArray(),
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

    public function actionUpdate($id)
    {
        try {
            $order = Order::find()
                ->byId($id)
                ->notDeleted()
                ->withDetails()
                ->one();

            if (!$order) {
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'Order not found.',
                ];
            }

            $order->scenario = Order::SCENARIO_UPDATE;
            $data = Yii::$app->request->post();

            if ($order->load($data, '')) {
                if ($order->save()) {
                    return [
                        'status' => true,
                        'data' => OrderResponse::fromModel($order)->toArray(),
                        'message' => 'Order updated successfully.',
                    ];
                }
            }

            return [
                'status' => false,
                'data' => $order->getErrors(),
                'message' => 'Validation failed: ' . json_encode($order->errors),
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'data' => null,
                'message' => 'Error updating order: ' . $th->getMessage(),
            ];
        }
    }

    public function actionDelete($id)
    {
        try {
            $order = Order::find()
                ->byId($id)
                ->notDeleted()
                ->one();

            if (!$order) {
                return [
                    'status'  => false,
                    'data'    => null,
                    'message' => 'Order not found.',
                ];
            }

            if ($order->softDelete()) {
                return [
                    'status'  => true,
                    'data'    => null,
                    'message' => 'Order deleted successfully.',
                ];
            }

            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Failed to delete order.',
            ];
        } catch (\Throwable $th) {
            return [
                'status'  => false,
                'data'    => null,
                'message' => 'Error deleting order: ' . $th->getMessage(),
            ];
        }
    }
}
