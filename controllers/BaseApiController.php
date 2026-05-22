<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\auth\HttpBearerAuth;

class BaseApiController extends Controller
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
}
