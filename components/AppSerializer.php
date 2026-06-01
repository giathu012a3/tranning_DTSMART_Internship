<?php

namespace app\components;

use yii\rest\Serializer;
use yii\data\DataProviderInterface;

class AppSerializer extends Serializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data)
    {
        if (is_array($data) && isset($data['status'])) {
            return $data;
        }

        $result = parent::serialize($data);

        if ($data instanceof DataProviderInterface) {
            return [
                'status'     => true,
                'message'    => 'Success',
                'data'       => $result[$this->collectionEnvelope] ?? [],
                'pagination' => $result[$this->metaEnvelope] ?? null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Success',
            'data'    => $result,
        ];
    }
}
