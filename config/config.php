<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'database' => 'ecommerce_v2'
    ],
    'google' => [
        'client_id' => '932071323991-87k4jsu4bh90o5u259f98n090v853g2h.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' => 'http://localhost/login/glogin.php'  // Must match exactly what's in Google Console
    ],
    'paths' => [
        'templates' => __DIR__ . '/../templates',
        'includes' => __DIR__ . '/../includes'
    ],
    'security' => [
        'password_min_length' => 8,
        'session_timeout' => 3600
    ],
    'defaults' => [
        'profile_image' => '/assets/img/Generic avatar.svg'
    ]
];