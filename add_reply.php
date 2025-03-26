<?php
/*
* add_reply.php
*
* Ito ang processor ng replies sa comments
* - Nag-hahandle ng POST request galing sa reply forms
* - Nag-sasave ng reply sa database
* - May parent-child relationship sa comments
*
* Konektado sa:
* - comments.php (source ng form data)
* - config/database.php (para sa database)
*/
session_start(); // Nagsisimula ng session para sa user authentication
require_once 'config/database.php'; // Nag-lo-load ng database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { // Nagche-check kung may user_id sa session
    header('Location: login.php');
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid security token');
}

// Check if parent_id is provided
if (!isset($_POST['parent_id'])) {
    die('Missing parent ID');
}

$parent_id = $_POST['parent_id'];
$content = $_POST['cmt_content'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate content
if (empty(trim($content))) {
    die('Reply content cannot be empty');
}

// Handle file upload if present
$attachment = null;
if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] == 0) {
    // File upload handling code...
    // Validate file type, size, etc.
    
    $attachment = 'attachments/' . time() . '_' . $_FILES['cmt_attachment']['name'];
    move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], 'uploads/' . $attachment);
}

try {
    // Insert the reply
    $stmt = $db->prepare("
        INSERT INTO tblcomments (cmt_content, cmt_isReply_to, cmt_added_by, cmt_attachment) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$content, $parent_id, $user_id, $attachment]);
    
    // Redirect back to the comments page
    header('Location: comments.php');
    exit;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} 