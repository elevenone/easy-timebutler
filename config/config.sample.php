<?php

return [
    'app' => [
        'enc_key' => 'foobar',
    ],

    'logger' => [
        'path_logs' => __DIR__ . '/../logs',
        'min_level' => 'warning',
    ],

    'db' => [
        'connections' => [
            'db1' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'easy_timebutler',
                'username' => '',
                'password' => '',
                'charset' => 'utf8', // Optional
                'timezone' => 'Europe/Berlin', // Optional
            ],

            // add additional connections here...
        ],

        'default_connection' => 'db1',
    ],

    'timebutler' => [
        'tmp_dir' => __DIR__ . '/../tmp',
    ]
];
