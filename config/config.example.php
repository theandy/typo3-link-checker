<?php

return [

    'database' => [
        'host' => 'localhost',
        'dbname' => 'typo3_db',
        'user' => 'typo3_user',
        'password' => 'secret'
    ],

    'typo3' => [
        'root_path' => '/var/www/project/'
    ],

    'mail' => [
        'to' => 'admin@example.com',

        'smtp' => [
            'host' => 'mail.example.com',
            'port' => 587,
            'username' => 'monitor@example.com',
            'password' => 'PASSWORD',
            'encryption' => 'tls'
        ],

        'from' => [
            'address' => 'monitor@example.com',
            'name' => 'TYPO3 Monitor'
        ]
    ],

    'log' => [
        'file' => __DIR__ . '/../logs/link-checker.log',
        'overwrite' => true
    ]

];