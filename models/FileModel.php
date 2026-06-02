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
        return Yii::$app->request->hostInfo . '/' . ltrim($path, '/');
    }
}
