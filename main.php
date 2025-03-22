<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }

        .comment-button:hover {
            background-color: #0056b3;
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
        
        <!-- Comment button -->
        <button class="comment-button" id="openComments">
            <img src="assets/images/comment-icon.png" alt="Comment" style="width: 20px; vertical-align: middle; margin-right: 5px;">
            Add Comment
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