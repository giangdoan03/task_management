<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/routes/web.php';

// index.php hoặc web.php (nơi mà request được xử lý)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Kiểm tra nếu yêu cầu là phương thức OPTIONS (Preflight request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Đáp ứng cho preflight request (yêu cầu kiểm tra trước)
    http_response_code(200);
    exit();
}