<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/routes/web.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();