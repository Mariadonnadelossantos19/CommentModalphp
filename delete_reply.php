<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a reply.']);
    exit;
}

// Check for CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

// Validate reply ID
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing reply ID.']);
    exit;
}

$reply_id = intval($_POST['id']);
$current_user_id = $_SESSION['user_id'];

// Check if the user owns this reply or is an admin
$stmt = $db->prepare("SELECT cmt_added_by FROM tblcomments WHERE cmt_id = ?");
$stmt->execute([$reply_id]);
$reply = $stmt->fetch(PDO::FETCH_ASSOC);

$is_admin = ($_SESSION['user_id'] == 1); // Assuming admin has ID 1

if (!$reply || ($reply['cmt_added_by'] != $current_user_id && !$is_admin)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this reply.']);
    exit;
}

// Get all nested replies to also delete
function getAllReplies($db, $reply_id, &$ids_to_delete) {
    $stmt = $db->prepare("SELECT cmt_id FROM tblcomments WHERE cmt_isReply_to = ?");
    $stmt->execute([$reply_id]);
    $nested_replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($nested_replies as $nested) {
        $ids_to_delete[] = $nested['cmt_id'];
        getAllReplies($db, $nested['cmt_id'], $ids_to_delete);
    }
}

try {
    $db->beginTransaction();
    
    // Get all nested replies
    $ids_to_delete = [$reply_id];
    getAllReplies($db, $reply_id, $ids_to_delete);
    
    // Delete all nested replies and the main reply
    $placeholders = str_repeat('?,', count($ids_to_delete) - 1) . '?';
    $stmt = $db->prepare("DELETE FROM tblcomments WHERE cmt_id IN ($placeholders)");
    $result = $stmt->execute($ids_to_delete);
    
    if ($result) {
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Reply deleted successfully.']);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete reply.']);
    }
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 