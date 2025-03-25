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
session_start(); // Nagsisimula ng session para sa user authentication
require_once 'config/database.php'; // Nag-lo-load ng database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { // Nagche-check kung may user_id sa session
    header('Content-Type: application/json'); // Naglalagay ng JSON header
    echo json_encode(['success' => false, 'message' => 'User not logged in']); // Nagbabalik ng error message kung hindi naka-login
    exit; // Humihinto ang script
}

// Check if comment_id was provided
if (!isset($_POST['comment_id'])) { // Nagche-check kung may binigay na comment ID
    header('Content-Type: application/json'); // Naglalagay ng JSON header
    echo json_encode(['success' => false, 'message' => 'Comment ID not provided']); // Nagbabalik ng error message kung walang ID
    exit; // Humihinto ang script
}

$comment_id = $_POST['comment_id']; // Kinukuha ang comment ID
$user_id = $_SESSION['user_id']; // Kinukuha ang ID ng kasalukuyang user

// First check if the comment belongs to the current user
$check_stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?"); // Query para kunin ang may-ari ng comment
$check_stmt->execute([$comment_id]); // Nag-e-execute ng query
$comment = $check_stmt->fetch(PDO::FETCH_ASSOC); // Kinukuha ang result bilang array

if (!$comment) { // Nagche-check kung umiiral ang comment
    header('Content-Type: application/json'); // Naglalagay ng JSON header
    echo json_encode(['success' => false, 'message' => 'Comment not found']); // Nagbabalik ng error message kung walang comment
    exit; // Humihinto ang script
}

// If comment doesn't belong to current user, deny deletion
if ($comment['cmt_added_by'] != $user_id) { // Nagche-check kung hindi sa user ang comment
    header('Content-Type: application/json'); // Naglalagay ng JSON header
    echo json_encode(['success' => false, 'message' => 'You can only delete your own comments']); // Nagbabalik ng error message
    exit; // Humihinto ang script
}

// If we're here, the user owns the comment, so delete it
$stmt = $db->prepare("DELETE FROM tblcomments WHERE cmt_id = ?"); // Query para i-delete ang comment
$success = $stmt->execute([$comment_id]); // Nag-e-execute ng query, at nag-store ng result

header('Content-Type: application/json'); // Naglalagay ng JSON header
echo json_encode(['success' => $success]); // Nagbabalik ng result
?> 