<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'name' => 'HealSlots',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
<<<<<<< HEAD
    'modules' => [],
=======
    'modules' => [
            'api' => [
                'class' => 'backend\modules\api\ModuleAPI',
            ]
    ],
>>>>>>> origin/filipe
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@app/views',
//                    '@app/views' => '@vendor/hail812/yii2-adminlte3/src/views'
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
<<<<<<< HEAD
=======
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/users' => 'api/users',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/salas' => 'api/salas',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/equipamentos' => 'api/equipamentos',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/tipoequipamentos' => 'api/tipoequipamentos',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/blocos' => 'api/blocos',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/manutencoes' => 'api/manutencoes',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/requisicoes' => 'api/requisicoes',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
>>>>>>> origin/filipe
            ],
        ],
    ],
    'params' => $params,
];