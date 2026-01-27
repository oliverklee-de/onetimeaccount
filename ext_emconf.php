<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a short-lived FE user account without having to enter a user name or password.',
    'version' => '7.2.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.4.99',
            'typo3' => '11.5.41-12.4.99',
            'extbase' => '11.5.41-12.4.99',
            'fluid' => '11.5.41-12.4.99',
            'feuserextrafields' => '6.7.0-6.99.99',
            'oelib' => '6.0.0-6.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'seminars' => '',
        ],
    ],
    'state' => 'stable',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'psr-4' => [
            'OliverKlee\\Onetimeaccount\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\Onetimeaccount\\Tests\\' => 'Tests/',
        ],
    ],
];
