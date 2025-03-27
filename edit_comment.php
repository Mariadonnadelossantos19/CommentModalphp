<?php
session_start();
require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to edit comments.']);
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and validate the data
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    
    if (empty($comment_id) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Comment ID and content are required.']);
        exit;
    }
    
    // Check if user owns the comment
    $check_query = "SELECT cmt_added_by FROM tblcomments WHERE cmt_id = $comment_id";
    $result = mysqli_query($conn, $check_query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Comment not found.']);
        exit;
    }
    
    $comment = mysqli_fetch_assoc($result);
    $is_admin = ($_SESSION['user_id'] == 1); // Simple admin check
    
    if ($comment['cmt_added_by'] != $_SESSION['user_id'] && !$is_admin) {
        echo json_encode(['success' => false, 'message' => 'You don\'t have permission to edit this comment.']);
        exit;
    }
    
    // Sanitize content
    $content = mysqli_real_escape_string($conn, $content);
    
    // Update the comment
    $update_query = "UPDATE tblcomments SET cmt_content = '$content' WHERE cmt_id = $comment_id";
    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['success' => true, 'message' => 'Comment updated successfully.', 'content' => $content]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating comment: ' . mysqli_error($conn)]);
    }
    exit;
}

// If it's not a POST request, return an error
echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit;
?> 