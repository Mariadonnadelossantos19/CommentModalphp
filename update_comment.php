<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['comment_id']) || !isset($_POST['cmt_content'])) {
            throw new Exception('Missing required fields');
        }

        $comment_id = $_POST['comment_id'];
        
        // Check if user owns this comment
        $stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($comment['cmt_added_by'] != $_SESSION['user_id']) {
            throw new Exception('You can only edit your own comments');
        }

        $content = $_POST['cmt_content'];
        $attachment = null;

        // Handle file upload
        if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === 0) {
            $upload_dir = 'uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = uniqid() . '_' . $_FILES['cmt_attachment']['name'];
            if (!move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], $upload_dir . $file_name)) {
                throw new Exception('Failed to upload file');
            }
            $attachment = $file_name;

            // Update with new attachment
            $stmt = $db->prepare("UPDATE tblcomments SET cmt_content = ?, cmt_attachment = ?, updated_at = NOW() WHERE cmt_id = ?");
            $stmt->execute([$content, $attachment, $comment_id]);
        } else {
            // Update without changing attachment
            $stmt = $db->prepare("UPDATE tblcomments SET cmt_content = ?, updated_at = NOW() WHERE cmt_id = ?");
            $stmt->execute([$content, $comment_id]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'post_data' => $_POST,
            'files' => $_FILES
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} 