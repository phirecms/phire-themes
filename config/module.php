<?php
/**
 * Module Name: phire-themes
 * Author: Nick Sagona
 * Description: This is the themes module for Phire CMS 2, to be used in conjunction with the Content module
 * Version: 1.0
 */
return [
    'phire-themes' => [
        'prefix'     => 'Phire\Themes\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'nav.phire'  => [
            'themes' => [
                'name' => 'Themes',
                'href' => '/themes',
                'acl' => [
                    'resource'   => 'themes',
                    'permission' => 'index'
                ],
                'attributes' => [
                    'class' => 'themes-nav-icon'
                ]
            ]
        ],
        'install' => function() {
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes')) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes');
                copy(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html',
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/index.html'
                );
                chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes', 0777);
                chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes/index.html', 0777);
            }
        },
        'uninstall' => function() {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes')) {
                $dir = new \Pop\File\Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/themes');
                $dir->emptyDir(true);
            }
        },
        'events' => [
            [
                'name'     => 'app.route.pre',
                'action'   => 'Phire\Themes\Event\Theme::bootstrap',
                'priority' => 1000
            ],
            [
                'name'     => 'app.send.pre',
                'action'   => 'Phire\Themes\Event\Theme::setTemplate',
                'priority' => 1000
            ]
        ],
        'updates'   => true,
        'invisible' => [
            'search.php',
            'search.phtml',
            'sidebar.php',
            'sidebar.phtml',
            'category.php',
            'category.phtml',
            'tag.php',
            'tag.phtml',
            'date.php',
            'date.phtml',
            'error.php',
            'error.phtml',
            'header.php',
            'header.phtml',
            'footer.php',
            'footer.phtml',
            'functions.php'
        ]
    ]
];
