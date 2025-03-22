<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['cmt_content'];
    $funding_id = $_POST['cmt_fnd_id'];
    $added_by = $_SESSION['user_id']; // Use logged-in user's ID
    $attachment = null;

    // Handle file upload
    if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === 0) {
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . $_FILES['cmt_attachment']['name'];
        move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], $upload_dir . $file_name);
        $attachment = $file_name;
    }

    $stmt = $db->prepare("INSERT INTO tblcomments (cmt_fnd_id, cmt_content, cmt_attachment, cmt_added_by, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$funding_id, $content, $attachment, $added_by]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
    exit;
} 