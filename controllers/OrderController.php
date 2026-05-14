<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
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

    /**
     * Creates a new Order (Checkout).
     * @return array
     */
    public function actionCreate()
    {
        try {
            $form = new OrderForm();

            // Expected JSON payload:
            // {
            //   "user_id": 1,
            //   "full_name": "Nguyen Van A",
            //   "email": "a@example.com",
            //   "phone": "0987654321",
            //   "address": "123 ABC Street",
            //   "payment_method": "COD",
            //   "coupon_code": "DISCOUNT10", // optional
            //   "items": [
            //     {"product_id": 1, "quantity": 2},
            //     {"product_id": 3, "quantity": 1}
            //   ]
            // }
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
}
