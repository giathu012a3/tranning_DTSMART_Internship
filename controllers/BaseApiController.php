<?php

namespace app\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;

class BaseApiController extends Controller
{
    public $serializer = [
        'class' => 'app\components\AppSerializer',
        'collectionEnvelope' => 'items',
        'metaEnvelope' => 'pagination',
    ];

    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();
    //     $behaviors['authenticator'] = [
    //         'class' => HttpBearerAuth::class,
    //     ];
    //     return $behaviors;
    // }

    /**
     * Helper to return standard success response.
     *
     * @param mixed $data
     * @param string $message
     * @param mixed|null $pagination
     * @return array
     */
    public function responseSuccess($data, string $message = 'Success', $pagination = null): array
    {
        $response = [
            'status'  => true,
            'data'    => $data,
            'message' => $message,
        ];
        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }
        return $response;
    }

    /**
     * Helper to return standard error response.
     *
     * @param string $message
     * @param mixed|null $data
     * @return array
     */
    public function responseError(string $message = 'Error', $data = null): array
    {
        return [
            'status'  => false,
            'data'    => $data,
            'message' => $message,
        ];
    }
}
