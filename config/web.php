<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '23456789',

            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'uploader' => [
            'class' => \app\components\UploadComponent::class,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // 'GET api/orders' => 'order/index',
                // 'GET api/products/<id:\d+>' => 'product/view',
                // 'POST api/orders'              => 'order/create',
                // 'GET api/orders/<id:\d+>'      => 'order/view',
                // 'DELETE api/orders/<id:\d+>'   => 'order/delete',
                // 'GET api/orders'               => 'order/index',
                // 'GET api/products/featured' => 'product/featured',
                // 'PUT api/categories/<id:\d+>'=>'category/update',
                // 'GET api/articles/<slug:>' => 'article/view',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'category',
                    'prefix' => 'api',
                    'pluralize' => true,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'product',
                    'prefix' => 'api',
                    'pluralize' => true,
                    'extraPatterns' => [
                        'POST {id}' => 'update',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'article',
                    'prefix' => 'api',
                    'pluralize' => true,
                    'extraPatterns' => [
                        'POST {id}' => 'update',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'order',
                    'prefix' => 'api',
                    'pluralize' => true,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user-order',
                    'prefix' => 'api',
                    'pluralize' => true,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'cart',
                    'prefix' => 'api',
                    'pluralize' => true,
                    'extraPatterns' => [
                        'DELETE' => 'clear',
                    ],
                ],


            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
