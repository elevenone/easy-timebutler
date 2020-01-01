<?php

return [
    // homepage
    'home' => [
        'method' => 'GET',
        'pattern' => '/',
        'handler' => 'Nekudo\EasyTimebutler\Actions\ShowHomeAction',
    ],

    // login
    'show_login' => [
        'method' => 'GET',
        'pattern' => '/login',
        'handler' => 'Nekudo\EasyTimebutler\Actions\Xhr\ShowLoginFormAction',
    ],

    'do_login' => [
        'method' => 'POST',
        'pattern' => '/login',
        'handler' => 'Nekudo\EasyTimebutler\Actions\Xhr\LoginAction',
    ],

    // dashboard
    'show_dashboard' => [
        'method' => 'GET',
        'pattern' => '/dashboard',
        'handler' => 'Nekudo\EasyTimebutler\Actions\Xhr\ShowDashboardAction',
    ],
];
