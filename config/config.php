<?php

return [

    'database' => [
        'host' => 'localhost',
        'dbname' => 'typo3_db',
        'user' => 'typo3_user',
        'password' => 'secret'
    ],

    'typo3' => [
        'root_path' => '/var/www/clients/client59/web1210/web/'
    ],

    'mail' => [

        'to' => 'loewer@werbestudio-mack.de',

        'smtp' => [
            'host' => 'mail.werbestudio-mack.de',
            'port' => 587,
            'username' => 'monitor@werbestudio-mack.de',
            'password' => 'PASSWORT',
            'encryption' => 'tls'
        ],

        'from' => [
            'address' => 'monitor@werbestudio-mack.de',
            'name' => 'TYPO3 Site Monitor'
        ]

    ],

    'log' => [
        'file' => __DIR__ . '/../logs/link-checker.log',
        'overwrite' => true
    ]

];