<?php
use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Controllers\UserController;

$authController = new AuthController();
$taskController = new TaskController();
$userController = new UserController();

// Lấy URI và loại bỏ phần tiền tố '/task_management'
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = str_replace('/task_management', '', $uri);

/**
 * Kiểm tra và trả về userId nếu token hợp lệ
 * @return mixed $userId nếu hợp lệ, false nếu không hợp lệ
 */
function authenticateUser($authController) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return $authController->validateToken($token);
    }
    return false;
}

// Xử lý các route
switch ($uri) {
    // Route cho login với cả phương thức GET và POST
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
    case '/tasks/getAllTasks':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = authenticateUser($authController);
            if ($userId) {
                $taskController->index();
            } else {
                echo json_encode(['error' => 'Unauthorized']);
            }
        }
        break;
    case (preg_match('/\/tasks\/(\d+)/', $uri, $matches) ? true : false):
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = authenticateUser($authController);
            if ($userId) {
                $taskController->getTaskById($matches[1]); // Truyền id từ URL vào hàm
            } else {
                echo json_encode(['error' => 'Unauthorized']);
            }
        }
        break;

    // Route cho lưu công việc mới và cập nhật các công việc
    case '/tasks/store':
    case '/tasks/update':
    case '/tasks/storeSubTask':
    case '/tasks/updateSubTaskCompletion':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = authenticateUser($authController);
            if ($userId) {
                // Phân nhánh theo route
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
                echo json_encode(['error' => 'Unauthorized']);
            }
        }
        break;

    // Route cho danh sách user
    case '/users':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = authenticateUser($authController);
            if ($userId) {
                $userController->index();
            } else {
                echo json_encode(['error' => 'Unauthorized']);
            }
        }
        break;

    // Route không tìm thấy
    default:
        echo json_encode(['error' => 'Route not found']);
        break;
}


