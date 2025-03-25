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
    echo json_encode(['success' => false, 'message' => 'User not authenticated']); // Nagbabalik ng error message kung hindi naka-login
    exit; // Humihinto ang script
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Checking kung POST request
    $content = $_POST['cmt_content']; // Kinukuha ang reply content
    $parent_id = $_POST['parent_id']; // Kinukuha ang ID ng comment na rineply-an
    $added_by = $_SESSION['user_id']; // Kinukuha ang ID ng user
    $attachment = null; // Default value para sa attachment

    // Get the funding_id from parent comment
    $stmt = $db->prepare("SELECT cmt_fnd_id FROM tblcomments WHERE cmt_id = ?"); // Query para kunin ang funding ID ng parent comment
    $stmt->execute([$parent_id]); // Nag-e-execute ng query
    $parent = $stmt->fetch(PDO::FETCH_ASSOC); // Kinukuha ang parent comment data
    $funding_id = $parent['cmt_fnd_id']; // Kinukuha ang funding ID

    // Handle file upload
    if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === 0) { // Checking kung may na-upload na file
        $upload_dir = 'uploads/'; // Folder kung saan ilalagay ang files
        $file_name = uniqid() . '_' . $_FILES['cmt_attachment']['name']; // Gumagawa ng unique filename
        move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], $upload_dir . $file_name); // Nagli-lipat ng file sa uploads folder
        $attachment = $file_name; // Nagse-set ng filename sa variable
    }

    // Insert reply
    $stmt = $db->prepare("INSERT INTO tblcomments (cmt_fnd_id, cmt_content, cmt_attachment, cmt_added_by, cmt_isReply_to, created_at) VALUES (?, ?, ?, ?, ?, NOW())"); // Query para i-save ang reply
    $stmt->execute([$funding_id, $content, $attachment, $added_by, $parent_id]); // Nag-e-execute ng query

    // After successful insertion
    header('Content-Type: application/json'); // Naglalagay ng JSON header
    echo json_encode(['success' => true, 'message' => 'Reply added successfully']); // Nagbabalik ng success message
    exit; // Humihinto ang script
} 