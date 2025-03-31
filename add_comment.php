<?php
/*
* add_comment.php
*
* Ito ang processor ng bagong comments
* - Nag-hahandle ng POST request galing sa comment form
* - Nag-sasave ng comment sa database
* - Nag-hahandle ng file uploads
*
* Konektado sa:
* - comments.php (source ng form data)
* - config/database.php (para sa database)
* - uploads folder (para sa attachments)
*/
session_start(); // Nagsisimula ng session para sa user authentication
require_once 'config/database.php'; // Nag-lo-load ng database connection

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set header for AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to add comments.";
    header('Location: comments.php');
    exit;
}

// Validate the comment content
if (empty($_POST['cmt_content'])) {
    $_SESSION['error'] = "Comment content cannot be empty.";
    header('Location: comments.php');
    exit;
}

// Check if the database connection is working
if (!isset($conn) || !$conn) {
    $_SESSION['error'] = "Database connection failed: " . mysqli_connect_error();
    header('Location: comments.php');
    exit;
}

// Handle file upload if present
$attachment = '';
if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = uniqid() . '_' . basename($_FILES['cmt_attachment']['name']);
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], $target_path)) {
        $attachment = $file_name;
    } else {
        $_SESSION['error'] = "Error uploading file.";
        header('Location: comments.php');
        exit;
    }
}

try {
    // Sanitize inputs
    $content = mysqli_real_escape_string($conn, $_POST['cmt_content']);
    $user_id = (int)$_SESSION['user_id'];
    $attachment = mysqli_real_escape_string($conn, $attachment);
    $fund_id = isset($_POST['cmt_fnd_id']) ? (int)$_POST['cmt_fnd_id'] : 1;
    
    // Insert the comment
    $query = "INSERT INTO tblcomments (cmt_content, cmt_added_by, cmt_attachment, created_at) 
              VALUES ('$content', $user_id, '$attachment', NOW())";
    
    if (mysqli_query($conn, $query)) {
        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'Comment added successfully.']);
        } else {
            $_SESSION['success'] = "Comment added successfully.";
            header('Location: comments.php');
        }
    } else {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Error adding comment: ' . mysqli_error($conn)]);
        } else {
            $_SESSION['error'] = "Error adding comment: " . mysqli_error($conn);
            header('Location: comments.php');
        }
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

exit;
?> 