<?php
// Thiết lập tiêu đề CORS
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Xử lý yêu cầu OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Controllers\UserController;

$authController = new AuthController();
$taskController = new TaskController();
$userController = new UserController();

// Lấy URI và loại bỏ phần tiền tố '/task_management'
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = str_replace('/task_management', '', $uri);

// Kiểm tra xác thực người dùng trước khi xử lý route
$userId = authenticateUser($authController);

/**
 * Phản hồi lỗi dưới dạng JSON
 * @param $message
 * @param int $statusCode
 */
function jsonResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['error' => $message]);
    exit();
}

/**
 * Kiểm tra và trả về userId nếu token hợp lệ
 * @param $authController
 * @return bool
 */
function authenticateUser($authController) {
    // Lấy tất cả header từ request
    $headers = apache_request_headers();

    // Kiểm tra xem header Authorization có tồn tại không
    if (isset($headers['Authorization'])) {
        // Tách token từ chuỗi Authorization header (dạng 'Bearer {token}')
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        // Xác thực token bằng phương thức validateToken
        $userId = $authController->validateToken($token);

        // Nếu token hợp lệ, trả về userId
        if ($userId) {
            return $userId;
        } else {
            // Nếu token không hợp lệ
            return false;
        }
    }

    // Trả về false nếu không có Authorization header
    return false;
}


// Xử lý route cho chi tiết công việc `/tasks/{id}`
if (preg_match('/^\/tasks\/(\d+)$/', $uri, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($userId) {
            $taskController->getTaskById($matches[1]);
        } else {
            jsonResponse('Unauthorized', 401);
        }
    }
    exit(); // Thoát sau khi xử lý route này
}

// Xử lý các route khác
switch ($uri) {
    // Route cho login
    case '/login':
        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
            $authController->login();
        }
        break;

    // Route cho register
    case '/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->register();
        }
        break;

    // Route cho logout
    case '/logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->logout();
        }
        break;

    // Route cho danh sách công việc
    case '/tasks':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($userId) {
                $taskController->getAllTasks();
            } else {
                jsonResponse('Unauthorized', 401);
            }
        }
        break;

    // Route cho lưu công việc mới và cập nhật các công việc
    case '/tasks/store':
    case '/tasks/update':
    case '/tasks/storeSubTask':
    case '/tasks/updateSubTaskCompletion':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($userId) {
                switch ($uri) {
                    case '/tasks/store':
                        $taskController->store();
                        break;
                    case '/tasks/update':
                        $taskController->update();
                        break;
                    case '/tasks/storeSubTask':
                        $taskController->storeSubTask();
                        break;
                    case '/tasks/updateSubTaskCompletion':
                        $taskController->updateSubTaskCompletion();
                        break;
                }
            } else {
                jsonResponse('Unauthorized', 401);
            }
        }
        break;

    case '/subtasks/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($userId) {
                $taskController->deleteSubTasks();
            } else {
                jsonResponse('Unauthorized', 401);
            }
        }
        break;

    // Route cho danh sách user
    case '/users':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($userId) {
                $userController->index();
            } else {
                jsonResponse('Unauthorized', 401);
            }
        }
        break;

    // Route không tìm thấy
    default:
        jsonResponse('Route not found', 404);
        break;
}
