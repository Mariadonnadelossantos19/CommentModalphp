<?php
/*
* add_comment.php
*
* Ito ang processor ng bagong comments
* - Nag-hahandle ng POST request galing sa comment form
* - Nag-sasave ng comment sa database
* - Nag-hahandle ng file uploads
*
* Konektado sa:
* - comments.php (source ng form data)
* - config/database.php (para sa database)
* - uploads folder (para sa attachments)
*/
session_start(); // Nagsisimula ng session para sa user authentication
require_once 'config/database.php'; // Nag-lo-load ng database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) { // Nagche-check kung may user_id sa session
    echo json_encode(['success' => false, 'message' => 'User not authenticated']); // Nagbabalik ng error message kung hindi naka-login
    exit; // Humihinto ang script kung hindi naka-login
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Checking kung POST request
    $content = $_POST['cmt_content']; // Kinukuha ang comment content mula sa form
    $funding_id = $_POST['cmt_fnd_id']; // Kinukuha ang funding ID na kino-comment-an
    $added_by = $_SESSION['user_id']; // Kinukuha ang ID ng user mula sa session
    $attachment = null; // Nagseset ng default value para sa attachment

    // Handle file upload
    if (isset($_FILES['cmt_attachment']) && $_FILES['cmt_attachment']['error'] === 0) { // Checking kung may na-upload na file
        $upload_dir = 'uploads/'; // Folder kung saan ilalagay ang files
        $file_name = uniqid() . '_' . $_FILES['cmt_attachment']['name']; // Unique filename para sa attachment
        move_uploaded_file($_FILES['cmt_attachment']['tmp_name'], $upload_dir . $file_name); // Nagli-lipat ng file sa uploads folder
        $attachment = $file_name; // Nag-sa-save ng filename sa variable
    }

    $stmt = $db->prepare("INSERT INTO tblcomments (cmt_fnd_id, cmt_content, cmt_attachment, cmt_added_by, created_at) VALUES (?, ?, ?, ?, NOW())"); // Naghahanda ng SQL query para mag-save ng comment
    $stmt->execute([$funding_id, $content, $attachment, $added_by]); // Nag-e-execute ng query kasama ang data

    header('Content-Type: application/json'); // Naglalagay ng JSON header sa response
    echo json_encode(['success' => true, 'message' => 'Comment added successfully']); // Nagbabalik ng success message sa JSON format
    exit; // Humihinto ang script pagkatapos mag-save
} 