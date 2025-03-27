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

// Check if it's an AJAX request or a regular request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set header based on request type
if ($isAjax) {
    header('Content-Type: application/json');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to delete comments.']);
    } else {
        $_SESSION['error'] = "You must be logged in to delete comments.";
        header('Location: comments.php');
    }
    exit;
}

// Get the comment ID
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 
             (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Check if the comment ID is valid
if ($comment_id <= 0) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
    } else {
        $_SESSION['error'] = "Invalid comment ID.";
        header('Location: comments.php');
    }
    exit;
}

// Check if this is a confirmation request (non-AJAX)
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] == 'yes';

// If not confirmed and not AJAX, show confirmation page
if (!$confirmed && !$isAjax) {
    // Get the comment data
    $query = "SELECT c.*, u.name as user_name 
              FROM tblcomments c 
              LEFT JOIN users u ON c.cmt_added_by = u.id 
              WHERE c.cmt_id = $comment_id";
    $result = mysqli_query($conn, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Comment not found.";
        header('Location: comments.php');
        exit;
    }
    
    $comment = mysqli_fetch_assoc($result);
    $is_admin = ($_SESSION['user_id'] == 1);
    
    // Check permission
    if ($comment['cmt_added_by'] != $_SESSION['user_id'] && !$is_admin) {
        $_SESSION['error'] = "You don't have permission to delete this comment.";
        header('Location: comments.php');
        exit;
    }
    
    // Determine if it's a comment or reply
    $type = $comment['cmt_isReply_to'] ? 'reply' : 'comment';
    
    // Show confirmation page
    include 'includes/header.php';
    ?>
    <div class="container">
        <div class="delete-confirmation">
            <h2>Delete <?php echo ucfirst($type); ?></h2>
            <p>Are you sure you want to delete this <?php echo $type; ?>? This action cannot be undone.</p>
            
            <div class="comment-preview">
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                </div>
                <div class="comment-content">
                    <?php echo htmlspecialchars($comment['cmt_content']); ?>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="delete_comment.php?id=<?php echo $comment_id; ?>&confirm=yes" class="btn btn-delete">Delete</a>
                <a href="comments.php" class="btn btn-cancel">Cancel</a>
            </div>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

// If we get here, either it's confirmed or AJAX - proceed with deletion

// Check if user owns the comment
$check_query = "SELECT cmt_added_by, cmt_isReply_to FROM tblcomments WHERE cmt_id = $comment_id";
$result = mysqli_query($conn, $check_query);

if (!$result || mysqli_num_rows($result) == 0) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Comment not found.']);
    } else {
        $_SESSION['error'] = "Comment not found.";
        header('Location: comments.php');
    }
    exit;
}

$comment = mysqli_fetch_assoc($result);
$is_admin = ($_SESSION['user_id'] == 1); // Simple admin check

if ($comment['cmt_added_by'] != $_SESSION['user_id'] && !$is_admin) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'You don\'t have permission to delete this comment.']);
    } else {
        $_SESSION['error'] = "You don't have permission to delete this comment.";
        header('Location: comments.php');
    }
    exit;
}

// Delete any replies to this comment first
$delete_replies_query = "DELETE FROM tblcomments WHERE cmt_isReply_to = $comment_id";
mysqli_query($conn, $delete_replies_query);

// Delete the comment
$delete_query = "DELETE FROM tblcomments WHERE cmt_id = $comment_id";
$delete_result = mysqli_query($conn, $delete_query);

if ($delete_result) {
    if ($isAjax) {
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully.']);
    } else {
        $_SESSION['success'] = "Comment deleted successfully.";
        header('Location: comments.php');
    }
} else {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Error deleting comment: ' . mysqli_error($conn)]);
    } else {
        $_SESSION['error'] = "Error deleting comment: " . mysqli_error($conn);
        header('Location: comments.php');
    }
}

mysqli_close($conn);
exit;
?> 