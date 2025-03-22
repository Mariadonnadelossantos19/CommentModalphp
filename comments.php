<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show login form
    header('Location: login.php');
    exit;
}

// Get current user info
$current_user_id = $_SESSION['user_id'];
$current_user_name = $_SESSION['user_name'] ?? 'Unknown User';

// Add this after session_start()
echo "<!-- Current user ID: " . $_SESSION['user_id'] . " -->";

// Add this after getting user info
$is_admin = ($_SESSION['user_id'] == 1); // Or check against a list of admin IDs

// Get comments with user information
$stmt = $db->prepare("
    SELECT c.*, u.name as user_name, COALESCE(u.avatar, 'default-avatar.png') as avatar 
    FROM tblcomments c 
    LEFT JOIN users u ON c.cmt_added_by = u.id 
    WHERE c.cmt_isReply_to IS NULL 
    ORDER BY c.created_at DESC
");
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<!-- Debug: Number of comments: " . count($comments) . " -->";
// And update the debug output for comments
echo "<!-- Debug: ";
var_dump($comments);
echo " -->";

// Get replies for each comment
function getReplies($db, $comment_id) {
    $stmt = $db->prepare("
        SELECT c.*, u.name as user_name, COALESCE(u.avatar, 'default-avatar.png') as avatar 
        FROM tblcomments c 
        LEFT JOIN users u ON c.cmt_added_by = u.id 
        WHERE c.cmt_isReply_to = ? 
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comments System</title>
    <link rel="stylesheet" href="css/comments.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="user-info">
                <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.png'); ?>" class="avatar" alt="User avatar">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </div>
            <div class="nav-actions">
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>
    </div>
    <div class="container">
        <!-- Add New Comment -->
        <div class="comment-section">
            <div class="add-comment-section">
                <form action="add_comment.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cmt_fnd_id" value="1">
                    <textarea name="cmt_content" class="comment-content" placeholder="Write your comment..."></textarea>
                    <div class="comment-actions">
                        <input type="file" name="cmt_attachment" class="upload-attachment" accept="image/*,.pdf,.doc,.docx">
                        <button type="submit" class="btn reply-comment">Post Comment</button>
                    </div>
                </form>
            </div>

            <!-- Comments List -->
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <div class="no-comments">
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?php echo $comment['cmt_id']; ?>">
                            <div class="comment-content">
                                <!-- Parent Comment -->
                                <div class="comment-header">
                                    <div class="user-info">
                                        <img src="uploads/avatars/<?php echo htmlspecialchars($comment['avatar'] ?? 'default-avatar.png'); ?>" class="avatar" alt="User avatar">
                                        <span class="user-name"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                                    </div>
                                    <span class="comment-date"><?php echo $comment['created_at']; ?></span>
                                </div>
                                <div class="comment-text"><?php echo htmlspecialchars($comment['cmt_content']); ?></div>
                                <?php if($comment['cmt_attachment']): ?>
                                    <div class="comment-attachment">
                                        <a href="uploads/<?php echo $comment['cmt_attachment']; ?>" target="_blank">
                                            View Attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="comment-actions">
                                    <button class="btn edit-comment">Edit</button>
                                    <button class="btn delete-comment">Delete</button>
                                    <button class="btn reply">Reply</button>
                                </div>

                                <!-- Reply Form for this comment -->
                                <div class="reply-form" style="display: none;">
                                    <form action="add_reply.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="parent_id" value="<?php echo $comment['cmt_id']; ?>">
                                        <textarea name="cmt_content" class="reply-content" placeholder="Write your reply..."></textarea>
                                        <div class="reply-actions">
                                            <input type="file" name="cmt_attachment" class="upload-reply" accept="image/*,.pdf,.doc,.docx">
                                            <button type="submit" class="btn send-reply">Send Reply</button>
                                            <button type="button" class="btn cancel-reply">Cancel</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Replies for this comment -->
                                <div class="replies-container">
                                    <?php 
                                    $replies = getReplies($db, $comment['cmt_id']);
                                    foreach($replies as $reply): 
                                    ?>
                                        <div class="reply-item" data-reply-id="<?php echo $reply['cmt_id']; ?>">
                                            <div class="reply-content">
                                                <div class="reply-header">
                                                    <div class="user-info">
                                                        <img src="uploads/avatars/<?php echo htmlspecialchars($reply['avatar'] ?? 'default-avatar.png'); ?>" class="avatar" alt="User avatar">
                                                        <span class="user-name"><?php echo htmlspecialchars($reply['user_name']); ?></span>
                                                    </div>
                                                    <span class="reply-date"><?php echo $reply['created_at']; ?></span>
                                                </div>
                                                <div class="reply-text"><?php echo htmlspecialchars($reply['cmt_content']); ?></div>
                                                <?php if($reply['cmt_attachment']): ?>
                                                    <div class="reply-attachment">
                                                        <a href="uploads/<?php echo $reply['cmt_attachment']; ?>" target="_blank">
                                                            View Attachment
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="reply-actions">
                                                    <?php if ($reply['cmt_added_by'] == $_SESSION['user_id'] || $is_admin): ?>
                                                        <button class="btn edit-reply">Edit</button>
                                                        <button class="btn delete-reply">Delete</button>
                                                    <?php endif; ?>
                                                    <button class="btn reply">Reply</button>
                                                </div>

                                                <!-- Add nested reply form -->
                                                <div class="reply-form nested-reply-form" style="display: none;">
                                                    <form action="add_reply.php" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="parent_id" value="<?php echo $reply['cmt_id']; ?>">
                                                        <textarea name="cmt_content" class="reply-content" placeholder="Write your reply..."></textarea>
                                                        <div class="reply-actions">
                                                            <input type="file" name="cmt_attachment" class="upload-reply" accept="image/*,.pdf,.doc,.docx">
                                                            <button type="submit" class="btn send-reply">Send Reply</button>
                                                            <button type="button" class="btn cancel-reply">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>

                                                <!-- Add container for nested replies -->
                                                <div class="nested-replies-container">
                                                    <?php 
                                                    $nested_replies = getReplies($db, $reply['cmt_id']);
                                                    foreach($nested_replies as $nested_reply): 
                                                    ?>
                                                        <div class="nested-reply-item" data-reply-id="<?php echo $nested_reply['cmt_id']; ?>">
                                                            <div class="reply-content">
                                                                <div class="reply-header">
                                                                    <div class="user-info">
                                                                        <img src="uploads/avatars/<?php echo htmlspecialchars($nested_reply['avatar'] ?? 'default-avatar.png'); ?>" class="avatar" alt="User avatar">
                                                                        <span class="user-name"><?php echo htmlspecialchars($nested_reply['user_name']); ?></span>
                                                                    </div>
                                                                    <span class="reply-date"><?php echo $nested_reply['created_at']; ?></span>
                                                                </div>
                                                                <div class="reply-text"><?php echo htmlspecialchars($nested_reply['cmt_content']); ?></div>
                                                                <?php if($nested_reply['cmt_attachment']): ?>
                                                                    <div class="reply-attachment">
                                                                        <a href="uploads/<?php echo $nested_reply['cmt_attachment']; ?>" target="_blank">
                                                                            View Attachment
                                                                        </a>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div class="reply-actions">
                                                                    <?php if ($nested_reply['cmt_added_by'] == $_SESSION['user_id'] || $is_admin): ?>
                                                                        <button class="btn edit-reply">Edit</button>
                                                                        <button class="btn delete-reply">Delete</button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="js/comments.js"></script>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Delete Confirmation</h3>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this comment?
            </div>
            <div class="modal-footer">
                <button class="btn" id="cancelDelete">Cancel</button>
                <button class="btn delete-comment" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="successToast" class="toast"></div>
</body>
</html> 