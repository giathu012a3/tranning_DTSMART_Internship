<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

class FileModel extends File
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * Build fully qualified URL for a file path.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function buildUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $hostInfo = (Yii::$app instanceof \yii\web\Application || Yii::$app->request instanceof \yii\web\Request)
            ? Yii::$app->request->hostInfo
            : 'http://localhost';
        return $hostInfo . '/' . ltrim($path, '/');
    }
}
