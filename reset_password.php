<?php
require_once 'config.php';

// Initialize variables
$token = "";
$error = "";
$message = "";
$show_form = false;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    // Verify token validity
    $stmt = $conn->prepare("SELECT id, username, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $expiry = strtotime($user['reset_token_expiry']);
        
        // Check if token has expired
        if (time() > $expiry) {
            $error = "Password reset link has expired. Please request a new one.";
        } else {
            $show_form = true;
            $username = $user['username'];
        }
    } else {
        $error = "Invalid password reset link. Please request a new one.";
    }
} else {
    $error = "No reset token provided.";
}

// Process password reset form
if ($_SERVER["REQUEST_METHOD"] == "POST" && $show_form) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Both password fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update user's password and clear reset token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        
        if ($stmt->execute()) {
            $message = "Your password has been reset successfully. You can now login with your new password.";
            $show_form = false;
        } else {
            $error = "Error updating password. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #000000;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('moviepics/login.jpg') center/cover;
        }

        .reset-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .badge-container {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .free-badge {
            background: #FFB800;
            color: rgb(0, 0, 0);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .flix-text {
            color: #FFB800;
            font-weight: 600;
            font-size: 12px;
            font-family: 'Angkor';
            font-size: 15px;
        }

        .reset-title {
            font-size: 28px;
            font-weight: 600;
            color: #FFB800;
            margin-bottom: 16px;
        }

        .reset-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            height: 45px;
            padding: 0 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            background: white;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: #9E9E9E;
        }

        .form-input:focus {
            outline: none;
            border-color: #FFB800;
            box-shadow: 0 0 0 2px rgba(255, 184, 0, 0.1);
        }
        
        .password-container {
            position: relative;
        }

        .password-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            position: absolute;
            right: 10px;
            top: 58%;
            transform: translateY(-50%);
            color: #666;
        }

        .password-toggle:hover {
            color: #333;
        }

        .submit-button {
            width: 100%;
            height: 45px;
            background: #FFB800;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-bottom: 20px;
        }

        .submit-button:hover {
            background: #E5A600;
        }

        .back-link {
            display: block;
            text-align: center;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
        }

        .back-link:hover {
            color: #FFB800;
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success-message {
            color: #2ecc71;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="reset-container">
        <div class="header">
            <div class="badge-container">
                <span class="free-badge">Free</span>
                <span class="flix-text">Flix</span>
            </div>
        </div>
        
        <h1 class="reset-title">Reset Password</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="success-message"><?php echo $message; ?></div>
            <a href="login.php" class="back-link">Back to Login</a>
        <?php elseif ($show_form): ?>
            <p class="reset-subtitle">Hello <?php echo $username; ?>, please enter your new password below.</p>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password"
                            class="form-input" 
                            placeholder="Enter your new password"
                            required
                            minlength="8"
                        >
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('new_password')">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path class="hide-password-1" fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                <path class="show-password-1" fill="currentColor" d="M12 6.5c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6.5 12 6.5zm-1.07 1.14L13 9.71c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.7.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="password-requirements">Password must be at least 8 characters long</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password"
                            class="form-input" 
                            placeholder="Confirm your new password"
                            required
                            minlength="8"
                        >
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('confirm_password')">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path class="hide-password-2" fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                <path class="show-password-2" fill="currentColor" d="M12 6.5c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6.5 12 6.5zm-1.07 1.14L13 9.71c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.7.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="submit-button">Reset Password</button>
            </form>
        <?php else: ?>
            <a href="forgot_password.php" class="back-link">Request a new password reset link</a>
        <?php endif; ?>
        
        <?php if (empty($message)): ?>
            <a href="login.php" class="back-link">Back to Login</a>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const number = inputId === 'new_password' ? '1' : '2';
            const showPasswordIcon = document.querySelector('.show-password-' + number);
            const hidePasswordIcon = document.querySelector('.hide-password-' + number);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showPasswordIcon.style.display = 'block';
                hidePasswordIcon.style.display = 'none';
            } else {
                passwordInput.type = 'password';
                showPasswordIcon.style.display = 'none';
                hidePasswordIcon.style.display = 'block';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const showPasswordIcon1 = document.querySelector('.show-password-1');
            const hidePasswordIcon1 = document.querySelector('.hide-password-1');
            const showPasswordIcon2 = document.querySelector('.show-password-2');
            const hidePasswordIcon2 = document.querySelector('.hide-password-2');
            
            if (showPasswordIcon1 && hidePasswordIcon1) {
                showPasswordIcon1.style.display = 'none';
            }
            
            if (showPasswordIcon2 && hidePasswordIcon2) {
                showPasswordIcon2.style.display = 'none';
            }
        });
    </script>
</body>
</html>