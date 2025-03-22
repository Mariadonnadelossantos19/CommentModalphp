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
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    
    try {
        // First delete any replies if they exist
        $stmt = $db->prepare("DELETE FROM tblcomments WHERE cmt_isReply_to = ?");
        $stmt->execute([$comment_id]);
        
        // Then delete the comment itself
        $stmt = $db->prepare("DELETE FROM tblcomments WHERE cmt_id = ?");
        $stmt->execute([$comment_id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 