<?php

return [
    APP_URI => [
        '/themes[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'index'
            ]
        ],
        '/themes/install[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'install',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'install'
            ]
        ],
        '/themes/process[/]' => [
            'controller' => 'Templates\Controller\IndexController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'themes',
                'permission' => 'process'
            ]
        ]
    ]
];
