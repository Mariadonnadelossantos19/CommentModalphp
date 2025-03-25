<?php
/*
* update_comment.php
*
* Ito ang processor para sa pag-update ng comments
* - Nag-hahandle ng edit requests
* - Nag-validate ng user permissions
* - Nag-update ng comment content at attachments
*
* Konektado sa:
* - comments.php (source ng edit requests)
* - config/database.php (para sa database)
* - uploads folder (para sa attachments)
*/
session_start(); // Nagsisimula ng session para sa user authentication
error_reporting(E_ALL); // Nagta-turn on ng lahat ng PHP errors
ini_set('display_errors', 1); // Nagse-set na ipakita ang errors
header('Content-Type: application/json'); // Nagta-tiyak na JSON ang response format

require_once 'config/database.php'; // Nag-lo-load ng database connection

if (!isset($_SESSION['user_id'])) { // Nagche-check kung may user_id sa session
    echo json_encode(['success' => false, 'message' => 'User not authenticated']); // Nagbabalik ng error message kung hindi naka-login
    exit; // Humihinto ang script
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Checking kung POST request
    try {
        if (!isset($_POST['comment_id']) || !isset($_POST['cmt_content'])) { // Checking kung kompleto ang data
            throw new Exception('Missing required fields'); // Nagtatapon ng error kung kulang ang fields
        }

        $comment_id = $_POST['comment_id']; // Kinukuha ang comment ID na ie-edit
        
        // Check if user owns this comment
        $stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?"); // Query para kunin ang may-ari ng comment
        $stmt->execute([$comment_id]); // Nag-e-execute ng query
        $comment = $stmt->fetch(PDO::FETCH_ASSOC); // Kinukuha ang comment data
        
        if ($comment['cmt_added_by'] != $_SESSION['user_id']) { // Checking kung ang user ang may-ari ng comment
            throw new Exception('You can only edit your own comments'); // Nagtatapon ng error kung hindi ang user ang may-ari
        }

        $content = $_POST['cmt_content']; // Kinukuha ang bagong content
        $attachment = null; // Default value para sa attachment

        // Handle file upload
        if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === 0) { // Checking kung may na-upload na file
            $upload_dir = 'uploads/'; // Folder kung saan ilalagay ang files
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($upload_dir)) { // Checking kung umiiral na ang uploads folder
                mkdir($upload_dir, 0777, true); // Gumagawa ng folder kung wala pa
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