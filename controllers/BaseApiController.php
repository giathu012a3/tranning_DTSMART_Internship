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

    /**
     * Helper to return success response with structured warnings for REST API (e.g. Vue).
     *
     * @param mixed $data
     * @param string $message
     * @param array $warnings
     * @return array
     */
    public function responseWithWarnings($data, string $message, array $warnings = []): array
    {
        return [
            'status'   => true,
            'message'  => $message,
            'data'     => $data,
            'warnings' => $warnings,
        ];
    }

    /**
     * Automatically extracts all validation/upload errors and custom errors (like tags) from a model
     * to structure them cleanly for REST API consumers.
     *
     * @param \yii\base\Model $model
     * @param array $customWarnings
     * @return array
     */
    public function extractWarnings($model, array $customWarnings = []): array
    {
        $warnings = [];

        foreach ($customWarnings as $key => $values) {
            if (!empty($values)) {
                $warnings[$key] = $values;
            }
        }

        if ($model->hasErrors()) {
            foreach ($model->getErrors() as $attribute => $errors) {
                if (!empty($errors)) {
                    $warnings[$attribute] = $errors;
                }
            }
        }
        return $warnings;
    }
}
