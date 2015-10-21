<?php

return [
    APP_URI => [
        '/themes[/]' => [
            'controller' => 'Phire\Themes\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'index'
            ]
        ],
        '/themes/install[/]' => [
            'controller' => 'Phire\Themes\Controller\IndexController',
            'action'     => 'install',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'install'
            ]
        ],
        '/themes/update/:id' => [
            'controller' => 'Phire\Themes\Controller\IndexController',
            'action'     => 'update',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'update'
            ]
        ],
        '/themes/process[/]' => [
            'controller' => 'Phire\Themes\Controller\IndexController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'process'
            ]
        ]
    ]
];
