<?php
require_once 'config.php';

// Initialize variables
$email = "";
$username = "";
$email_error = "";
$username_error = "";
$password_error = "";
$confirm_password_error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitizeInput($_POST["email"]);
    $username = sanitizeInput($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Simple validation
    if (empty($email)) {
        $email_error = "Email is required";
    }
    if (empty($username)) {
        $username_error = "Username is required";
    }
    if (empty($password)) {
        $password_error = "Password is required";
    } elseif (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long";
    }
    if ($password != $confirm_password) {
        $password_error = "Passwords do not match";
        $confirm_password_error = "Passwords do not match";
    }

    // Check if username or email already exists
    if (empty($email_error) && empty($username_error)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['username'] == $username) {
                $username_error = "Username already exists";
            }
            if ($row['email'] == $email) {
                $email_error = "Email already exists";
            }
        }
    }

    // If no errors, process registration
    if (empty($email_error) && empty($username_error) && empty($password_error) && empty($confirm_password_error)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the insert statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            // Trigger the pop-up instead of direct redirect
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('successPopup').style.display = 'flex';
                });
            </script>";
        } else {
            $email_error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
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
            z-index: -1;
        }

        .signup-container {
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
            gap: 193px;
            margin-bottom: 4px;
        }

        .welcome-text {
            font-size: 16px;
            color: #333;
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
            font-size: 13px;
            font-weight: 500;
        }

        .flix-text {
            color: #FFB800;
            font-weight: 600;
            font-size: 12px;
            font-family: 'Angkor';
            font-size: 15px;
        }

        .sign-up-text {
            font-size: 32px;
            font-weight: 600;
            color: #FFB800;
            margin-bottom: 24px;
            margin-top: auto;
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

        .form-input.email-error,
        .form-input.username-error,
        .form-input.password-error,
        .form-input.confirm-password-error {
            border-color: #FF0000;
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

        .register-button {
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

        .register-button:hover {
            background: #E5A600;
        }

        .login-text {
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .login-link {
            color: #000;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 0;
            text-align: left;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Pop-up Styles */
        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: #2D2D2D;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            max-width: 435px;
            width: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .popup-content h2 {
            color: #FFB800;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .popup-content p {
            color: #FFFFFF;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .popup-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .popup-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .popup-button.confirm {
            background: #FFB800;
            color: #000000;
        }

        .popup-button.confirm:hover {
            background: #E5A600;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="signup-container">
        <div class="header">
            <span class="welcome-text">Welcome !</span>
            <div class="badge-container">
                <span class="free-badge">Free</span>
                <span class="flix-text">Flix</span>
            </div>
        </div>
        
        <h1 class="sign-up-text">Sign up</h1>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="register-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input <?php echo !empty($email_error) ? 'email-error' : ''; ?>" 
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >
                <?php if (!empty($email_error)): ?>
                    <div class="error-message"><?php echo $email_error; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input <?php echo !empty($username_error) ? 'username-error' : ''; ?>" 
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required
                >
                <?php if (!empty($username_error)): ?>
                    <div class="error-message"><?php echo $username_error; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input <?php echo !empty($password_error) ? 'password-error' : ''; ?>" 
                        placeholder="Enter your Password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('password')">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path class="hide-password-1" fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            <path class="show-password-1" fill="currentColor" d="M12 6.5c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6.5 12 6.5zm-1.07 1.14L13 9.71c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.70.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z"/>
                        </svg>
                    </button>
                </div>
                <?php if (!empty($password_error)): ?>
                    <div class="error-message"><?php echo $password_error; ?></div>
                <?php endif; ?>
                <p class="password-requirements">Password must be at least 8 characters long</p>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="confirm-password" 
                        name="confirm_password" 
                        class="form-input <?php echo !empty($confirm_password_error) ? 'confirm-password-error' : ''; ?>" 
                        placeholder="Confirm your Password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('confirm-password')">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path class="hide-password-2" fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            <path class="show-password-2" fill="currentColor" d="M12 6.5c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6.5 12 6.5zm-1.07 1.14L13 9.71c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.70.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z"/>
                        </svg>
                    </button>
                </div>
                <?php if (!empty($confirm_password_error)): ?>
                    <div class="error-message"><?php echo $confirm_password_error; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="register-button">Register</button>
            
            <div class="login-text">
                Already have an Account ? <a href="login.php" class="login-link">Login</a>
            </div>
        </form>
    </div>
    
    <!-- Pop-up HTML -->
    <div id="successPopup" class="popup-container" style="display: none;">
        <div class="popup-content">
            <h2>Registration Successful</h2>
            <p>You have successfully registered! You will be redirected to the login page.</p>
            <div class="popup-buttons">
                <button id="confirmPopup" class="popup-button confirm">OK</button>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const number = inputId === 'password' ? '1' : '2';
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
            const showPasswordIcons = document.querySelectorAll('.show-password-1, .show-password-2');
            showPasswordIcons.forEach(icon => {
                icon.style.display = 'none';
            });
            
            // Client-side validation
            const form = document.getElementById('register-form');
            const emailInput = document.getElementById('email');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            
            form.addEventListener('submit', function(event) {
                let valid = true;
                let errorMessages = [];
                
                // Check email
                if (!emailInput.value) {
                    valid = false;
                    errorMessages.push("Email is required");
                }
                
                // Check username
                if (!usernameInput.value) {
                    valid = false;
                    errorMessages.push("Username is required");
                }
                
                // Check password length
                if (passwordInput.value.length < 8) {
                    valid = false;
                    errorMessages.push("Password must be at least 8 characters long");
                }
                
            });

            // Handle pop-up button click
            const confirmPopupButton = document.getElementById('confirmPopup');
            if (confirmPopupButton) {
                confirmPopupButton.addEventListener('click', function() {
                    window.location.href = 'login.php';
                });
            }
        });
    </script>
</body>
</html>