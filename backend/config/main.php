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
    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\ModuleAPI',
        ]
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@app/views',
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser', // ADICIONAR ESTA LINHA
            ],
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
                // API RESTful - Sala
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/sala' => 'api/sala', // SINGULAR
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                // API RESTful - Bloco
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/bloco' => 'api/bloco', // SINGULAR
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                // API RESTful - Requisição
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/requisicao' => 'api/requisicao', // SINGULAR
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
                // Para manter compatibilidade com URLs antigas
                'api/salas' => 'api/sala/index',
                'api/blocos' => 'api/bloco/index',
                'api/requisicoes' => 'api/requisicao/index',

                // Outras APIs (mantenha como estão)
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
                        'api/manutencoes' => 'api/manutencoes',
                    ],
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];