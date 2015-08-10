<?php

return [
    APP_URI => [
        '/themes[/]' => [
            'controller' => 'Themes\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'index'
            ]
        ],
        '/themes/install[/]' => [
            'controller' => 'Themes\Controller\IndexController',
            'action'     => 'install',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'install'
            ]
        ],
        '/themes/process[/]' => [
            'controller' => 'Themes\Controller\IndexController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'process'
            ]
        ]
    ]
];
