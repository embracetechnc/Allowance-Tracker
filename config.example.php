<?php
return [
    'app' => [
        'name' => 'Allowance Tracker',
        'timezone' => 'America/New_York',
        'debug' => true,
    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'allowance_tracker',
        'user' => 'your_db_user',
        'pass' => 'your_db_password',
    ],
    'jwt' => [
        'secret' => 'your_jwt_secret_key',
        'expiry' => 3600, // 1 hour
    ],
    'email' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password', // Gmail App Password
        'from_email' => 'your-email@gmail.com',
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