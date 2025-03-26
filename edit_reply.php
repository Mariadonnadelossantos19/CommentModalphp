<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to edit a reply.']);
    exit;
}

// Check for CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

// Validate required fields
if (!isset($_POST['id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

$reply_id = intval($_POST['id']);
$content = trim($_POST['content']);
$current_user_id = $_SESSION['user_id'];

// Make sure content is not empty
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Reply content cannot be empty.']);
    exit;
}

// Check if the user owns this reply or is an admin
$stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?");
$stmt->execute([$reply_id]);
$reply = $stmt->fetch(PDO::FETCH_ASSOC);

$is_admin = ($_SESSION['user_id'] == 1); // Assuming admin has ID 1

if (!$reply || ($reply['cmt_added_by'] != $current_user_id && !$is_admin)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this reply.']);
    exit;
}

// Update the reply
try {
    $stmt = $db->prepare("UPDATE tblcomments SET cmt_content = ? WHERE cmt_id = ?");
    $result = $stmt->execute([$content, $reply_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reply updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reply.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 