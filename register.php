<?php
/*
* register.php
*
* Ito ang registration page para sa mga bagong users
* - May registration form
* - Nag-validate ng user input
* - Nag-hash ng password
* - Nag-create ng bagong user sa database
*
* Konektado sa:
* - config/database.php (para sa database)
* - login.php (redirect after registration)
*/
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords match
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }

        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('Username already taken');
        }

        $avatar = 'default-avatar.png'; // Default avatar

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $upload_dir = 'uploads/avatars/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF allowed.');
            }

            $avatar = uniqid() . '_' . $_FILES['avatar']['name'];
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $avatar)) {
                throw new Exception('Failed to upload avatar');
            }
        }

        // Create new user with avatar
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, username, password, avatar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $username, $password_hash, $avatar]);

        // Auto login after registration
        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['user_name'] = $name;
        $_SESSION['avatar'] = $avatar;

        header('Location: comments.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/comments.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Register</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="form-group">
                    <label for="avatar">Profile Picture (optional)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="form-control">
                </div>
                <button type="submit" class="btn">Register</button>
                <p class="text-center">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html> 