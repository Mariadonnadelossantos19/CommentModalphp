<?php
/*
* comments.php
*
* Ito ang main page ng comment system
* - Nagpapakita ng comment form
* - Nagpapakita ng listahan ng comments at replies
* - May user authentication
* - May upload feature para sa attachments
* 
* Konektado sa:
* - add_comment.php (para mag-add ng comment)
* - add_reply.php (para mag-add ng reply)
* - delete_comment.php (para mag-delete ng comment)
* - config/database.php (para sa database connection)
* - js/comments.js (para sa frontend functionality)
*/
session_start(); // Nagsisimula ng session para sa user authentication
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // Nag-setup ng cache control para hindi mag-cache ang browser
header("Cache-Control: post-check=0, pre-check=0", false); // Additional cache control settings
header("Pragma: no-cache"); // Additional cache control para sa lumang browsers

require_once 'config/database.php'; // Nag-lo-load ng database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { // Nagche-check kung may user_id sa session
    // Redirect to login page or show login form
    header('Location: login.php'); // Nire-redirect sa login page kung hindi naka-login
    exit; // Humihinto ang script
}

// Get current user info
$current_user_id = $_SESSION['user_id']; // Kinukuha ang user ID mula sa session
$current_user_name = $_SESSION['user_name'] ?? 'Unknown User'; // Kinukuha ang username mula sa session o "Unknown User" kung wala

// Add this after session_start()
echo "<!-- Current user ID: " . $_SESSION['user_id'] . " -->"; // Naglalagay ng comment sa HTML para sa debugging

// Add this after getting user info
$is_admin = ($_SESSION['user_id'] == 1); // Checking kung admin ang user (ID = 1)

// Get comments with user information
$stmt = $db->prepare("
    SELECT c.*, u.name as user_name, COALESCE(u.avatar, 'default-avatar.png') as avatar 
    FROM tblcomments c 
    LEFT JOIN users u ON c.cmt_added_by = u.id 
    WHERE c.cmt_isReply_to IS NULL 
    ORDER BY c.created_at DESC
"); // Query para kunin ang lahat ng comments na hindi reply
$stmt->execute(); // Nag-e-execute ng query
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC); // Kinukuha ang lahat ng comments bilang array
echo "<!-- Debug: Number of comments: " . count($comments) . " -->"; // Debugging para sa bilang ng comments
// And update the debug output for comments
echo "<!-- Debug: ";
var_dump($comments); // Nagpa-print ng debug info sa HTML comment
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

// Add this function after the getReplies function
function getReplyCount($db, $comment_id) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM tblcomments
        WHERE cmt_isReply_to = ?
    ");
    $stmt->execute([$comment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Add this function near the top of the file after require_once
function ensureDirectoriesExist() {
    $dirs = [
        'assets/images',
        'uploads',
        'uploads/avatars',
        'uploads/attachments'
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Enhance the avatar path handling function
function getAvatarPath($avatar) {
    ensureDirectoriesExist();
    
    $default_avatar = 'assets/images/default-avatar.png';
    
    // Create default avatar if it doesn't exist
    if (!file_exists($default_avatar)) {
        // Create a simple default avatar or copy from another location
        copy('path/to/backup/default-avatar.png', $default_avatar);
    }
    
    if (empty($avatar)) {
        return $default_avatar;
    }
    
    $avatar_path = "uploads/avatars/" . $avatar;
    return file_exists($avatar_path) ? $avatar_path : $default_avatar;
}

// Add this function to help with file security
function isValidFileUpload($file) {
    $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}

// Add these utility functions
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('M j, Y g:i A', $timestamp);
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Add this near the top of the file
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Add this near the top
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . 
         htmlspecialchars($_SESSION['flash_message']) . 
         '</div>';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comments System</title>
    <link rel="stylesheet" href="css/comments.css">
    <style>
        /* Base styles and layout fixes */
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Enhanced navbar */
        .navbar {
            background: linear-gradient(135deg, #4e73df 0%, #3a57c9 100%);
            color: white;
            padding: 12px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Improved comment form */
        .add-comment-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: box-shadow 0.3s ease;
        }
        
        .add-comment-section:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .comment-content, textarea.reply-content {
            width: 100%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
            font-family: inherit;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .comment-content:focus, textarea.reply-content:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15);
            outline: none;
        }
        
        /* Enhanced comment styling */
        .comment-item, .reply-item, .nested-reply-item {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        
        .comment-item {
            border-left: 4px solid #4e73df;
        }
        
        .comment-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        /* Improved reply styling */
        .reply-item {
            margin-left: 40px;
            border-left: 4px solid #f6c23e;
            background-color: #fffdf7;
        }
        
        .nested-reply-item {
            margin-left: 40px;
            border-left: 4px solid #36b9cc;
            background-color: #f7fdff;
        }
        
        /* Avatar styling */
        .avatar {
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 45px;
            min-height: 45px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }
        
        .comment-item .avatar {
            background: linear-gradient(135deg, #4e73df 0%, #3a57c9 100%);
            color: white;
        }
        
        .reply-item .avatar {
            background: linear-gradient(135deg, #f6c23e 0%, #e6b325 100%);
            color: #5a4500;
        }
        
        .nested-reply-item .avatar {
            background: linear-gradient(135deg, #36b9cc 0%, #2aa3b9 100%);
            color: white;
        }
        
        /* Header and user info */
        .comment-header, .reply-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            width: 100%;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .comment-date, .reply-date {
            color: #6c757d;
            font-size: 13px;
            margin-left: auto;
            white-space: nowrap;
            font-style: italic;
        }
        
        /* Comment and reply text */
        .comment-text, .reply-text {
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 15px;
            word-break: break-word;
            color: #2c3e50;
        }
        
        /* Enhanced buttons and actions */
        .btn, button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .reply-comment, .send-reply {
            background: #4e73df;
            color: white;
            box-shadow: 0 2px 4px rgba(78, 115, 223, 0.2);
        }
        
        .reply-comment:hover, .send-reply:hover {
            background: #3a57c9;
            box-shadow: 0 3px 6px rgba(78, 115, 223, 0.3);
        }
        
        .cancel-reply {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #ddd;
        }
        
        .cancel-reply:hover {
            background: #e9ecef;
        }
        
        .edit-comment, .edit-reply {
            background: #36b9cc;
            color: white;
            margin-right: 8px;
        }
        
        .edit-comment:hover, .edit-reply:hover {
            background: #2aa3b9;
        }
        
        .delete-comment, .delete-reply {
            background: #e74a3b;
            color: white;
            margin-right: 8px;
        }
        
        .delete-comment:hover, .delete-reply:hover {
            background: #d52a1a;
        }
        
        .reply {
            background: #f6c23e;
            color: #5a4500;
            margin-right: 8px;
        }
        
        .reply:hover {
            background: #e6b325;
        }
        
        .comment-actions, .reply-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }
        
        /* Reply count badge */
        .reply-count {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .comment-item .reply-count {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            border: 1px solid rgba(78, 115, 223, 0.2);
        }
        
        .reply-item .reply-count {
            background-color: rgba(246, 194, 62, 0.1);
            color: #b08000;
            border: 1px solid rgba(246, 194, 62, 0.2);
        }
        
        /* File upload styling */
        input[type="file"] {
            padding: 8px;
            border-radius: 6px;
            border: 1px dashed #ced4da;
            background-color: #f8f9fa;
            width: auto;
            cursor: pointer;
        }
        
        /* Attachment styling */
        .comment-attachment a, .reply-attachment a {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            color: #4e73df;
            text-decoration: none;
            font-size: 13px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        
        .comment-attachment a:hover, .reply-attachment a:hover {
            background-color: #4e73df;
            color: white;
        }
        
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-icon {
            background-color: #f8d7da;
            color: #e74a3b;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            font-weight: bold;
        }
        
        .modal-title {
            margin-bottom: 25px;
            color: #2c3e50;
        }
        
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .btn-cancel {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #ddd;
            padding: 10px 20px;
        }
        
        .btn-delete {
            background-color: #e74a3b;
            color: white;
            padding: 10px 20px;
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #4e73df;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1001;
            font-weight: 500;
        }
        
        /* Hide navbar when in iframe */
        body.in-iframe .navbar {
            display: none;
        }
        
        body.in-iframe .container {
            padding-top: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .reply-item, .nested-reply-item {
                margin-left: 20px;
            }
            
            .comment-header, .reply-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .comment-date, .reply-date {
                margin-left: 0;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_GET['iframe']) ? 'in-iframe' : ''; ?>">
    <div class="navbar">
        <div class="container">
            <div class="user-info">
                <div class="avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
            <div>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>
    <div class="container">
        <!-- Add New Comment -->
        <div class="comment-section">
            <div class="add-comment-section">
                <form action="add_comment.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cmt_fnd_id" value="1">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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
                                <div class="comment-header">
                                    <div class="user-info">
                                        <div class="avatar">
                                            <?php echo strtoupper(substr($comment['user_name'], 0, 1)); ?>
                                        </div>
                                        <span class="user-name"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                                    </div>
                                    <span class="comment-date"><?php echo formatDate($comment['created_at']); ?></span>
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
                                    <?php if($comment['cmt_added_by'] == $_SESSION['user_id']): ?>
                                        <button class="edit-comment">Edit</button>
                                        <button class="delete-comment">Delete</button>
                                    <?php endif; ?>
                                    <button class="reply">Reply</button>
                                    
                                    <!-- Add the reply count here -->
                                    <?php 
                                    $replyCount = getReplyCount($db, $comment['cmt_id']); 
                                    if ($replyCount > 0):
                                    ?>
                                    <span class="reply-count"><?php echo $replyCount; ?> <?php echo $replyCount == 1 ? 'reply' : 'replies'; ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Reply Form for this comment -->
                                <div class="reply-form" style="display: none;">
                                    <form action="add_reply.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="parent_id" value="<?php echo $comment['cmt_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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
                                                        <div class="avatar">
                                                            <?php echo strtoupper(substr($reply['user_name'], 0, 1)); ?>
                                                        </div>
                                                        <span class="user-name"><?php echo htmlspecialchars($reply['user_name']); ?></span>
                                                    </div>
                                                    <span class="reply-date"><?php echo formatDate($reply['created_at']); ?></span>
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
                                                    <?php if ($reply['cmt_added_by'] == $_SESSION['user_id']): ?>
                                                        <button class="edit-reply">Edit</button>
                                                        <button class="delete-reply">Delete</button>
                                                    <?php endif; ?>
                                                    <button class="btn reply">Reply</button>
                                                    
                                                    <!-- Add the reply count here -->
                                                    <?php 
                                                    $nestedReplyCount = getReplyCount($db, $reply['cmt_id']); 
                                                    if ($nestedReplyCount > 0):
                                                    ?>
                                                    <span class="reply-count"><?php echo $nestedReplyCount; ?> <?php echo $nestedReplyCount == 1 ? 'reply' : 'replies'; ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Add nested reply form -->
                                                <div class="reply-form nested-reply-form" style="display: none;">
                                                    <form action="add_reply.php" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="parent_id" value="<?php echo $reply['cmt_id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
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
                                                                        <div class="avatar">
                                                                            <?php echo strtoupper(substr($nested_reply['user_name'], 0, 1)); ?>
                                                                        </div>
                                                                        <span class="user-name"><?php echo htmlspecialchars($nested_reply['user_name']); ?></span>
                                                                    </div>
                                                                    <span class="reply-date"><?php echo formatDate($nested_reply['created_at']); ?></span>
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
                                                                    <?php if ($nested_reply['cmt_added_by'] == $_SESSION['user_id']): ?>
                                                                        <button class="edit-reply">Edit</button>
                                                                        <button class="delete-reply">Delete</button>
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
            <div class="modal-icon">
                <i class="exclamation">!</i>
            </div>
            <h3 class="modal-title">Are you sure you want to delete this comment?</h3>
            <div class="modal-actions">
                <button class="btn btn-cancel" id="cancelDelete">Cancel</button>
                <button class="btn btn-delete" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="successToast" class="toast"></div>
</body>
</html> 