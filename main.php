<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the last comment/reply for this user
$stmt = $db->prepare("
    SELECT c.cmt_content, c.created_at, u.name as user_name 
    FROM tblcomments c 
    LEFT JOIN users u ON c.cmt_added_by = u.id 
    ORDER BY c.created_at DESC 
    LIMIT 1
");
$stmt->execute();
$lastComment = $stmt->fetch(PDO::FETCH_ASSOC);

// Format the date
function formatDate($date) {
    return date('d M Y h:i A', strtotime($date));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Main Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .comment-button {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-size: 16px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .comment-button:hover {
            background-color: #5a6268;
        }

        .comment-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            flex: 1;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .comment-details {
            flex: 1;
            overflow: hidden;
        }

        .comment-user {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .comment-text {
            font-size: 13px;
            color: #e9ecef;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            width: 90%;
            max-width: 1000px;
            height: 80vh;
            border-radius: 8px;
            position: relative;
        }

        .close-button {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 30px;
            cursor: pointer;
            color: #666;
        }

        .close-button:hover {
            color: #333;
        }

        iframe {
            width: 100%;
            height: calc(100% - 20px);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        
        <!-- Comment button with last comment -->
        <button class="comment-button" id="openComments">
            <?php if ($lastComment): ?>
                <div class="comment-preview">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($lastComment['user_name'], 0, 1)); ?>
                    </div>
                    <div class="comment-details">
                        <div class="comment-user"><?php echo htmlspecialchars($lastComment['user_name']); ?></div>
                        <div class="comment-text"><?php echo htmlspecialchars($lastComment['cmt_content']); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="comment-preview">
                    <div class="user-avatar">
                        <i class="fa fa-comment"></i>
                    </div>
                    <div class="comment-details">
                        <div class="comment-text">No comments yet. Click to add comment.</div>
                    </div>
                </div>
            <?php endif; ?>
        </button>
    </div>

    <!-- Modal for comments -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeModal">&times;</span>
            <iframe id="commentFrame" src=""></iframe>
        </div>
    </div>

    <script>
        const modal = document.getElementById('commentModal');
        const commentFrame = document.getElementById('commentFrame');
        const openButton = document.getElementById('openComments');
        const closeButton = document.getElementById('closeModal');

        openButton.onclick = function() {
            modal.style.display = 'block';
            commentFrame.src = 'comments.php?iframe=1';
        }

        closeButton.onclick = function() {
            modal.style.display = 'none';
            commentFrame.src = '';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                commentFrame.src = '';
            }
        }
    </script>
</body>
</html> 