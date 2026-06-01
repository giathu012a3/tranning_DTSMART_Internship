<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=training',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600 * 24 * 30,
    'schemaCache' => 'cache',
];
