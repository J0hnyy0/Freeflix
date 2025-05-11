<?php
require_once 'config.php';

// Initialize variables
$error = "";
$message = "";
$email_submitted = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
            // Generate unique reset token
            $token = bin2hex(random_bytes(32));
            
            // Set token expiration time (24 hours from now)
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Store token in database
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token, $expiry, $user_id);
            
            if ($update_stmt->execute()) {
                // In production, you would send an email with the reset link
                // For development purposes, we'll redirect directly to the reset page
                
                // Set email_submitted flag to true for redirect
                $email_submitted = true;
                
                // JavaScript redirect to reset_password.php with token
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'reset_password.php?token=" . $token . "';
                    }, 500); // Short delay for better user experience
                </script>";
                
                $message = "Processing your request...";
            } else {
                $error = "An error occurred. Please try again later.";
            }
        } else {
            // For security reasons, don't reveal if email exists or not
            // But we'll add a small delay to prevent email enumeration
            usleep(random_int(300000, 600000)); // Sleep between 0.3-0.6 seconds
            
            // Show success message even if email doesn't exist
            $email_submitted = true;
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php?message=" . urlencode("If your email is registered, you will receive password reset instructions shortly.") . "';
                }, 1500);
            </script>";
            $message = "Processing your request...";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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

        .forgot-container {
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

        .forgot-title {
            font-size: 28px;
            font-weight: 600;
            color: #FFB800;
            margin-bottom: 16px;
        }

        .forgot-subtitle {
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

        .submit-button:disabled {
            background: #cccccc;
            cursor: not-allowed;
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
        
        .loading {
            text-align: center;
            margin: 20px 0;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 184, 0, 0.3);
            border-radius: 50%;
            border-top-color: #FFB800;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="forgot-container">
        <div class="header">
            <div class="badge-container">
                <span class="free-badge">Free</span>
                <span class="flix-text">Flix</span>
            </div>
        </div>
        
        <h1 class="forgot-title">Forgot Password</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="success-message"><?php echo $message; ?></div>
            <div class="loading">
                <div class="loading-spinner"></div>
            </div>
        <?php endif; ?>
        
        <?php if (!$email_submitted): ?>
            <p class="forgot-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="forgot-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="form-input" 
                        placeholder="Enter your email address"
                        required
                    >
                </div>
                
                <button type="submit" class="submit-button" id="submit-btn">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <?php if (!$email_submitted): ?>
            <a href="login.php" class="back-link">Back to Login</a>
        <?php endif; ?>
    </div>
    
    <script>
        // Add loading state to form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgot-form');
            const submitBtn = document.getElementById('submit-btn');
            
            if (form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                });
            }
        });
    </script>
</body>
</html>