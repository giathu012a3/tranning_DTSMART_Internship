<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=127.0.0.1;dbname=training',
    'username' => 'root',
    'password' => '123456789',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600 * 24 * 30,
    'schemaCache' => 'cache',
];
