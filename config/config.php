<?php
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_DATABASE') ?: 'task_management',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ]
];

