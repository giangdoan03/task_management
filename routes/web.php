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
use App\Database; // Import class Database
use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Controllers\UserController;
use App\Controllers\CommentController;


// Lấy kết nối cơ sở dữ liệu từ Singleton Database
$db = Database::getInstance(); // Khởi tạo kết nối cơ sở dữ liệu từ lớp Database

$authController = new AuthController();
$taskController = new TaskController();
$userController = new UserController();
$commentController = new CommentController($db); // Truyền $db vào CommentController
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
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $userId = $authController->validateToken($token);
        return $userId ?: false;
    }
    return false;
}

// Kiểm tra xác thực người dùng
function checkAuthentication($userId) {
    if (!$userId) {
        jsonResponse('Unauthorized', 401);
    }
}

// Lấy ID từ POST data
function getPostId() {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        return $_POST['id'];
    }
    jsonResponse('ID is required', 400);
}

// Xử lý route cho chi tiết công việc `/tasks/{id}`
if (preg_match('/^\/tasks\/(\d+)$/', $uri, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        checkAuthentication($userId);
        $taskController->getTaskById($matches[1]);
    }
    exit(); // Thoát sau khi xử lý route này
}

// Route cho chi tiết người dùng `/users/{id}`
if (preg_match('/^\/users\/(\d+)$/', $uri, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        checkAuthentication($userId);
        $userController->show($matches[1]); // Gọi phương thức show với ID người dùng
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
            checkAuthentication($userId);
            $taskController->getAllTasks();
        }
        break;

    // Route cho lưu công việc mới và cập nhật các công việc
    case '/tasks/store':
    case '/tasks/update':
    case '/tasks/storeSubTask':
    case '/tasks/updateSubTaskCompletion':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);
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
        }
        break;

    case '/subtasks/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);
            $taskController->deleteSubTasks();
        }
        break;

    // Route cho danh sách user
    case '/users':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            checkAuthentication($userId);
            $userController->index();
        }
        break;

    case '/users/store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);  // Kiểm tra xác thực người dùng
            $userController->store();  // Gọi phương thức store để tạo người dùng mới
        }
        break;

    // Route cho cập nhật thông tin người dùng
    case '/users/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);
            $id = getPostId(); // Lấy ID từ POST
            $userController->update($id);
        }
        break;

    // Route cho xóa người dùng
    case '/users/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);
            $id = getPostId(); // Lấy ID từ POST
            $userController->delete($id);
        }
        break;

    // Route để lấy danh sách bình luận cho công việc
    case '/comments/task':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            checkAuthentication($userId);
            $taskId = $_GET['task_id']; // Lấy task_id từ query params
            $commentController->getComments($taskId);
        }
        break;

    // Route để lưu bình luận mới
    case '/comments/store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkAuthentication($userId);
            $commentController->storeComment();
        }
        break;



    // Route không tìm thấy
    default:
        jsonResponse('Route not found', 404);
        break;
}
