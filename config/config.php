<?php

return [

    'database' => [
        'host' => 'localhost',
        'dbname' => 'typo3',
        'user' => 'dbuser',
        'password' => 'secret'
    ],

    'typo3' => [
        'root_path' => '/var/www/typo3project/'
    ],

    'mail' => [
        'to' => 'admin@example.com',
        'from' => 'monitor@example.com'
    ],

    'log' => [
        'file' => __DIR__ . '/../logs/link-checker.log',

        // true = Log bei jedem Start neu schreiben
        // false = Log anhängen
        'overwrite' => true
    ]

];