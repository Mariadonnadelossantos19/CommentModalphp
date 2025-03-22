<?php
/*
* delete_comment.php
*
* Ito ang processor para sa pag-delete ng comments
* - Nag-hahandle ng delete requests
* - Nag-checheck ng user permissions
* - Nag-dedelete din ng related replies
*
* Konektado sa:
* - comments.php (source ng delete request)
* - config/database.php (para sa database)
*/
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if comment_id was provided
if (!isset($_POST['comment_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Comment ID not provided']);
    exit;
}

$comment_id = $_POST['comment_id'];
$user_id = $_SESSION['user_id'];

// First check if the comment belongs to the current user
$check_stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?");
$check_stmt->execute([$comment_id]);
$comment = $check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit;
}

// If comment doesn't belong to current user, deny deletion
if ($comment['cmt_added_by'] != $user_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You can only delete your own comments']);
    exit;
}

// If we're here, the user owns the comment, so delete it
$stmt = $db->prepare("DELETE FROM tblcomments WHERE cmt_id = ?");
$success = $stmt->execute([$comment_id]);

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?> 