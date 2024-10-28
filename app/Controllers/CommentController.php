<?php

namespace App\Controllers;

use App\Models\Comment;

class CommentController
{
    private $commentModel;

    private $db;

    public function __construct($db)
    {
        $this->commentModel = new Comment($db);
    }

    // Lấy danh sách bình luận của một công việc

    public function getComments($taskId)
    {
        $comments = $this->commentModel->getCommentsByTaskId($taskId);
        echo json_encode($comments);
    }

    // Tạo bình luận mới
    public function storeComment()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['task_id']) || !isset($data['user_id']) || !isset($data['content'])) {
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Kiểm tra nếu user_id và task_id tồn tại
        if (!$this->commentModel->userExists($data['user_id'])) {
            echo json_encode(['error' => 'User does not exist']);
            return;
        }

        if (!$this->commentModel->taskExists($data['task_id'])) {
            echo json_encode(['error' => 'Task does not exist']);
            return;
        }

        // Nếu cả hai tồn tại, tiến hành tạo bình luận
        $this->commentModel->createComment($data);
        echo json_encode(['success' => 'Comment added successfully']);
    }

}
