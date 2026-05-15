<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Order;
use app\models\forms\OrderForm;
use app\models\response\OrderResponse;
use app\models\search\OrderSearch;
use yii\filters\auth\HttpBearerAuth;

class OrderController extends Controller
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

    public function actionCreate()
    {
        try {
            $form = new OrderForm();
            $data = Yii::$app->request->post();

            if ($form->load($data, '')) {
                $order = $form->save();
                if ($order !== false) {
                    $orderResponse = OrderResponse::findOne($order->id);
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

    /**
     * Lists orders.
     */
    public function actionIndex()
    {
        try {
            $searchModel = new OrderSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

            $data = array_map(function ($model) {
                $response = new OrderResponse();
                $response->setAttributes($model->attributes, false);
                $response->populateRelation('orderDetails', $model->orderDetails);
                return $response->toArray([], ['orderDetails']);
            }, $dataProvider->getModels());

            return [
                'status' => true,
                'data' => [
                    'items' => $data,
                    'pagination' => [
                        'total_count' => $dataProvider->getTotalCount(),
                        'page_count' => $dataProvider->pagination->getPageCount(),
                        'current_page' => $dataProvider->pagination->getPage() + 1,
                        'per_page' => $dataProvider->pagination->getPageSize(),
                    ],
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
                ->byUser(Yii::$app->user->id)
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

            $response = new OrderResponse();
            $response->setAttributes($order->attributes, false);
            $response->populateRelation('orderDetails', $order->orderDetails);
            $response->populateRelation('couponUsage', $order->couponUsage);

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

    public function actionDelete($id)
    {
        try {
            $order = Order::find()
                ->byId($id)
                ->byUser(Yii::$app->user->id)
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
