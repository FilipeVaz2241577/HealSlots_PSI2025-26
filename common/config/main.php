<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // 'cache' => 'cache', // opcional - se quiser usar cache para RBAC
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        // ... outros componentes se necessário
    ],
];