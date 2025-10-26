<?php
return [
    'app' => [
        'name' => 'Allowance Tracker',
        'timezone' => 'America/New_York',
        'debug' => true,
        'url' => 'http://localhost:8000',
    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'allowance_tracker',
        'user' => 'root',
        'pass' => '',
    ],
    'jwt' => [
        'secret' => 'your_jwt_secret_key',
        'expiry' => 3600, // 1 hour
    ],
    'email' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'embracetechnologync@gmail.com',
        'password' => 'ajlq jhfj mhcs zjgo', // Enter your 16-character App Password here
        'from_email' => 'embracetechnologync@gmail.com',
        'from_name' => 'Allowance Tracker',
        'encryption' => 'tls',
    ],
    'bible_api' => [
        'url' => 'https://bible-api.com',
        'version' => 'kjv',
    ],
    'cashapp' => [
        'client_id' => 'your_cashapp_client_id',
        'client_secret' => 'your_cashapp_client_secret',
        'redirect_uri' => 'http://localhost:8000/cashapp/callback',
    ],
];