<?php
use App\Controllers\AuthController;
use App\Controllers\TaskController;

$authController = new AuthController();
$taskController = new TaskController();

$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = str_replace('/task_management', '', $uri);


//echo $uri;

// Route cho login với cả phương thức GET và POST
if ($uri === '/login' && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
//    echo 'xxx';
    $authController->login();
}

// Route cho register
elseif ($uri === '/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->register();
}

// Route cho logout
elseif ($uri === '/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->logout();
}

// Route cho tasks (chỉ xem danh sách công việc)
elseif ($uri === '/tasks' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy token từ tiêu đề Authorization
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $userId = $authController->validateToken($token);

        if ($userId) {
            $taskController->index();
        } else {
            echo json_encode(['error' => 'Unauthorized']);
        }
    } else {
        echo json_encode(['error' => 'No token provided']);
    }
}

// Route cho lưu công việc mới
elseif ($uri === '/tasks/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy token từ tiêu đề Authorization
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $userId = $authController->validateToken($token);

        if ($userId) {
            $taskController->store();
        } else {
            echo json_encode(['error' => 'Unauthorized']);
        }
    } else {
        echo json_encode(['error' => 'No token provided']);
    }
}

// Route không tìm thấy
else {
    echo json_encode(['error' => 'Route not found']);
}
