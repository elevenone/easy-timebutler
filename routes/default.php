<?php

return [
    // homepage
    'home' => [
        'method' => 'GET',
        'pattern' => '/',
        'handler' => 'Nekudo\EasyTimebutler\Actions\ShowHomeAction',
    ],

    // login
    'do_login' => [
        'method' => 'POST',
        'pattern' => '/login',
        'handler' => 'Nekudo\EasyTimebutler\Actions\Xhr\LoginAction',
    ],

    // stopclock actions
    'stopclock' => [
        'method' => 'POST',
        'pattern' => '/stopclock',
        'handler' => 'Nekudo\EasyTimebutler\Actions\Xhr\InvokeStopclockAction',
    ],
];
