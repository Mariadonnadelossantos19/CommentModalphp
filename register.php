<?php
/*
* register.php
*
* Ito ang registration page ng system
* - Nag-hahandle ng user registration
* - May form validation
* - Nag-create ng new user account
* 
* Konektado sa:
* - config/database.php (para sa database connection)
* - login.php (redirect after registration)
*/
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if username is empty
    if (empty($username)) {
        $errors[] = "Username is required";
    } else {
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already taken";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Process avatar upload
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Validate file extension
        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Invalid file format. Only JPG, JPEG, PNG and GIF files are allowed.";
        }
        
        // Validate file size (max 2MB)
        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $errors[] = "File size exceeds limit of 2MB.";
        }
        
        if (empty($errors)) {
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads/avatars/')) {
                mkdir('uploads/avatars/', 0755, true);
            }
            
            // Generate unique filename
            $avatar = time() . '_' . basename($filename);
            $target = 'uploads/avatars/' . $avatar;
            
            // Upload file
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                $errors[] = "Failed to upload avatar.";
                $avatar = null;
            }
        }
    }
    
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Insert user into database
            $stmt = $db->prepare("INSERT INTO users (name, email, username, password, avatar) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $username, $hashed_password, $avatar]);
            
            $_SESSION['flash_message'] = "Registration successful! You can now log in.";
            $_SESSION['flash_type'] = "success";
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                // This handles the duplicate entry error
                $errors[] = "Username or email is already registered";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // If there are errors, store them in session
    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode('<br>', $errors);
        $_SESSION['flash_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            max-width: 450px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #6c757d;
            color: white;
            text-align: center;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem 1rem;
        }
        .btn-register {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: bold;
            padding: 0.6rem 1rem;
        }
        .btn-register:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .input-group-text {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .form-label {
            font-weight: 500;
        }
        .register-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .alert-danger ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .avatar-upload {
            position: relative;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .avatar-upload .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #f3f3f3;
            border: 2px solid #e0e0e0;
            margin: 0 auto 10px;
            position: relative;
            background-image: url('assets/images/default-avatar.png');
            background-size: cover;
            background-position: center;
        }
        
        .avatar-upload .avatar-edit {
            position: relative;
            margin-top: 10px;
        }
        
        .avatar-upload .avatar-edit input {
            display: none;
        }
        
        .avatar-upload .avatar-edit label {
            display: inline-block;
            width: 34px;
            height: 34px;
            background: #28a745;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            color: white;
            line-height: 34px;
            text-align: center;
            margin-right: 10px;
        }
        
        .avatar-text {
            font-size: 14px;
            color: #666;
        }
        
        .form-container {
            max-width: 500px;
            padding: 30px;
        }
        
        .icon {
            color: #28a745;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus register-logo"></i>
                <h4 class="mb-0">Create an Account</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert <?php echo $_SESSION['flash_type']; ?>">
                        <?php 
                        echo $_SESSION['flash_message']; 
                        unset($_SESSION['flash_message']);
                        unset($_SESSION['flash_type']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="avatar-upload">
                        <div class="avatar-preview" id="avatarPreview"></div>
                        <div class="avatar-edit">
                            <input type="file" id="avatarUpload" name="avatar" accept=".png, .jpg, .jpeg, .gif">
                            <label for="avatarUpload">+</label>
                            <span class="avatar-text">Upload Avatar (Optional)</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="form-text">Password must be at least 6 characters</div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-register btn-success">Register</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="login-link">
            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login here</a></p>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview avatar image before upload
        document.getElementById('avatarUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').style.backgroundImage = `url(${e.target.result})`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 