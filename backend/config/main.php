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
//                    '@app/views' => '@vendor/hail812/yii2-adminlte3/src/views'
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [ //faltava isto
                'application/json' => 'yii\web\JsonParser',
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
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/user' => 'api/user',
                    ],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'PUT {id}/profile' => 'update-profile',  // ← ADICIONE ESTA LINHA
                        'PATCH {id}/profile' => 'update-profile', // ← E ESTA LINHA
                    ],
                    'tokens' => [
                        '{id}' => '<id:\\d+>',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/sala' => 'api/sala'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'GET disponiveis' => 'disponiveis',
                        'GET {id}/detalhes' => 'detalhes',
                    ],
                    'tokens' => ['{id}' => '<id:\\d+>'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/equipamento' => 'api/equipamento'],
                    'pluralize' => false,
                    'extraPatterns' => [


                        'GET search' => 'search',
                        'GET disponiveis' => 'disponiveis',
                        'POST criar' => 'criar',
                        'GET verificar-serie' => 'verificar-serie',
                    ],
                    'tokens' => ['{id}' => '<id:\\d+>'],
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
                    'controller' => ['api/bloco' => 'api/bloco'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'GET {id}/salas' => 'salas',
                        'GET {id}/estatisticas' => 'estatisticas',
                    ],
                    'tokens' => ['{id}' => '<id:\\d+>'],
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
                    'controller' => ['api/requisicao' => 'api/requisicao'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'POST criar' => 'criar',
                        'GET minhas' => 'minhas',
                        'POST {id}/concluir' => 'concluir',
                        'POST {id}/cancelar' => 'cancelar',
                        'GET verificar-disponibilidade' => 'verificar-disponibilidade',
                    ],
                    'tokens' => ['{id}' => '<id:\\d+>'],
                ],
                // URLs compatíveis com plural
                'api/salas' => 'api/sala/index',
                'api/blocos' => 'api/bloco/index',
                'api/requisicoes' => 'api/requisicao/index',
                'api/equipamentos' => 'api/equipamento/index',
            ],
        ],
    ],
    'params' => $params,
];