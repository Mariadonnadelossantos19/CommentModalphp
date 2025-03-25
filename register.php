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

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Checking kung POST request
    $name = $_POST['name']; // Kinukuha ang name mula sa form
    $email = $_POST['email']; // Kinukuha ang email mula sa form
    $password = $_POST['password']; // Kinukuha ang password mula sa form
    $confirm_password = $_POST['confirm_password']; // Kinukuha ang password confirmation
    
    // Basic validation
    $errors = []; // Array para sa error messages
    
    if (empty($name)) { // Checking kung walang name
        $errors[] = "Name is required"; // Nagdadagdag ng error message
    }
    
    if (empty($email)) { // Checking kung walang email
        $errors[] = "Email is required"; // Nagdadagdag ng error message
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Checking kung valid ang email format
        $errors[] = "Invalid email format"; // Nagdadagdag ng error message
    }
    
    if (empty($password)) { // Checking kung walang password
        $errors[] = "Password is required"; // Nagdadagdag ng error message
    } elseif (strlen($password) < 6) { // Checking kung masyadong maikli ang password
        $errors[] = "Password must be at least 6 characters"; // Nagdadagdag ng error message
    }
    
    if ($password !== $confirm_password) { // Checking kung hindi match ang passwords
        $errors[] = "Passwords do not match"; // Nagdadagdag ng error message
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?"); // Query para i-check kung may existing user
    $stmt->execute([$email]); // Nag-e-execute ng query
    $user = $stmt->fetch(); // Kinukuha ang result
    
    if ($user) { // Checking kung may existing user na
        $errors[] = "Email already in use"; // Nagdadagdag ng error message
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $success = $stmt->execute([$name, $email, $hashed_password]);
        
        if ($success) {
            $_SESSION['success_message'] = "Registration successful! You can now login.";
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
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
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
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
</body>
</html> 