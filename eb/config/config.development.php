<?php

return [
    'modules' => [
        'app' => [
            'class' => [
                'name' => '\eb\ElectroBunker',
                'components' => [
                    'controllers' => ['defaultControllerName' => 'home'],
                ],
            ],
        ],
        'resources' => [
            'class' => [
                'name' => '\gear\modules\resources\GResourcesModule',
                'plugins' => [
                    'js' => ['mappingFolder' => 'js'],
                    'css' => ['mappingFolder' => 'css'],
                ],
            ],
        ],
    ],
    'components' => [
        'db' => [
            'class' => '\gear\components\db\mysql\GMySqlConnectionComponent',
            'host' => 'localhost',
            'user' => '',
            'password' => '',
        ],
    ],
];
