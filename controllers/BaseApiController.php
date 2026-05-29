<?php

namespace app\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;

class BaseApiController extends Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
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
}
