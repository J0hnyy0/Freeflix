<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Prevent caching to ensure the login page isn't stored
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is already logged in
if (isLoggedIn()) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: homepage.php");
    }
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['fix_admin']) && $_GET['fix_admin'] == '1' && isset($_GET['secret']) && $_GET['secret'] == 'your_secret_key_123') {
    $admin_username = 'ADMIN';
    $admin_password = 'admin12345';
    $admin_email = 'admin@example.com';
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $is_admin = 1;

    $stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin, session_token, email, registration_date) VALUES (?, ?, ?, NULL, ?, NOW())");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssis", $admin_username, $hashed_password, $is_admin, $admin_email);
        if ($stmt->execute()) {
            echo "Admin user '$admin_username' created successfully with email '$admin_email'! Login with username: $admin_username, password: $admin_password.";
        } else {
            echo "Error creating admin user: " . $conn->error;
        }
    } else {
        // Update existing user to ensure is_admin = 1, correct password, and email
        $user = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE users SET is_admin = 1, password = ?, email = ? WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $hashed_password, $admin_email, $admin_username);
        if ($stmt->execute()) {
            echo "Admin user '$admin_username' updated with admin status, new password, and email '$admin_email'.";
        } else {
            echo "Error updating admin user: " . $conn->error;
        }
    }
    $stmt->close();
    exit();
}

if (isset($_GET['logout'])) {
    global $conn;
    if (isset($_SESSION['username'])) {
        $stmt = $conn->prepare("UPDATE users SET session_token = NULL WHERE username = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $stmt->close();
    }
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$username = "";
$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password
    $username = sanitizeInput($_POST["username"]);
    $password = $_POST["password"];
    
    if (empty($username)) {
        $username_error = "Username is required";
    }
    if (empty($password)) {
        $password_error = "Password is required";
    }
    
    if (empty($username_error) && empty($password_error)) {
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $session_token = bin2hex(random_bytes(16));
                
                $stmt = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("si", $session_token, $user['id']);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $_SESSION['session_token'] = $session_token;
                    
                    // Redirect to appropriate page
                    $redirect_url = $user['is_admin'] == 1 ? 'admin_dashboard.php' : 'homepage.php';
                    ?>
                    <script>
                        // Push current state to history to replace login page
                        history.pushState(null, null, '<?php echo $redirect_url; ?>');
                        // Redirect to prevent back button issues
                        window.location.replace('<?php echo $redirect_url; ?>');
                    </script>
                    <?php
                    exit();
                } else {
                    $password_error = "An error occurred. Please try again.";
                }
            } else {
                $password_error = "Invalid password";
            }
        } else {
            $username_error = "Invalid username";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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

.login-container {
    position: absolute;
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
    gap: 195px;
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

.sign-in-text {
    font-size: 32px;
    font-weight: 600;
    color: #FFB800;
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

.form-input.username-error,
.form-input.password-error {
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
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9E9E9E;
    padding: 4px;
}

.password-toggle:hover {
    color: #333;
}

.options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.remember-container {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.remember-container input {
    display: none;
}

.checkmark {
    width: 19px;
    height: 20px;
    border: 2px solid #E0E0E0;
    border-radius: 4px;
    position: relative;
}

.remember-container input:checked + .checkmark {
    background: #FFB800;
    border-color: #FFB800;
}

.remember-container input:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.remember-text {
    font-size: 14px;
    color: #333;
}

.forgot-link {
    color: #666;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-link:hover {
    color: #FFB800;
    text-decoration: underline;
}

.login-button {
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

.login-button:hover {
    background: #E5A600;
}

.register-text {
    text-align: center;
    color: #666;
    font-size: 14px;
}

.register-link {
    color: #000;
    text-decoration: none;
    font-weight: 500;
}

.register-link:hover {
    text-decoration: underline;
}

.error-message {
    color: #e74c3c;
    font-size: 14px;
    margin-top: 5px;
    margin-bottom: 0;
    text-align: left;
}
</style>
</head>
<body>
    <div class="background"></div>
    <div class="login-container">
        <div class="header">
            <span class="welcome-text">Welcome !</span>
            <div class="badge-container">
                <span class="free-badge">Free</span>
                <span class="flix-text">Flix</span>
            </div>
        </div>
        
        <h1 class="sign-in-text">Sign in</h1>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username"
                    class="form-input <?php echo !empty($username_error) ? 'username-error' : ''; ?>" 
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($username); ?>"
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
                    >
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword()">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path class="show-password" fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            <path class="hide-password" fill="currentColor" d="M12 6.5c3.79 0 7.17 2.13 8.82 5.5-.59 1.22-1.42 2.27-2.41 3.12l1.41 1.41c1.39-1.23 2.49-2.77 3.18-4.53C21.27 7.11 17 4 12 4c-1.27 0-2.49.2-3.64.57l1.65 1.65C10.66 6.09 11.32 6.5 12 6.5zm-1.07 1.14L13 9.71c.57.25 1.03.71 1.28 1.28l2.07 2.07c.08-.34.14-.70.14-1.07C16.5 9.01 14.48 7 12 7c-.37 0-.72.05-1.07.14zM2.01 3.87l2.68 2.68C3.06 7.83 1.77 9.53 1 11.5 2.73 15.89 7 19 12 19c1.52 0 2.98-.29 4.32-.82l3.42 3.42 1.41-1.41L3.42 2.45 2.01 3.87zm7.5 7.5l2.61 2.61c-.04.01-.08.02-.12.02-1.38 0-2.5-1.12-2.5-2.5 0-.05.01-.08.01-.13zm-3.4-3.4l1.75 1.75c-.23.55-.36 1.15-.36 1.78 0 2.48 2.02 4.5 4.5 4.5.63 0 1.23-.13 1.77-.36l.98.98c-.88.24-1.8.38-2.75.38-3.79 0-7.17-2.13-8.82-5.5.7-1.43 1.72-2.61 2.93-3.53z"/>
                        </svg>
                    </button>
                </div>
                <?php if (!empty($password_error)): ?>
                    <div class="error-message"><?php echo $password_error; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="options-row">
                <label class="remember-container">
                    <input type="checkbox" id="remember" name="remember">
                    <span class="checkmark"></span>
                    <span class="remember-text">Remember me</span>
                </label>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-button">Login</button>
            
            <div class="register-text">
                Don't have an Account ? <a href="register.php" class="register-link">Register</a>
            </div>
        </form>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showPasswordIcon = document.querySelector('.show-password');
            const hidePasswordIcon = document.querySelector('.hide-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showPasswordIcon.style.display = 'none';
                hidePasswordIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                showPasswordIcon.style.display = 'block';
                hidePasswordIcon.style.display = 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const showPasswordIcon = document.querySelector('.show-password');
            const hidePasswordIcon = document.querySelector('.hide-password');
            
            hidePasswordIcon.style.display = 'none';
            
            // Prevent back button from showing login page if session is active
            if (window.history && window.history.pushState) {
                window.history.pushState(null, null, window.location.href);
                window.onpopstate = function() {
                    // Redirect to homepage or admin dashboard based on session
                    fetch('check_session.php', { method: 'GET' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.isLoggedIn) {
                                window.location.replace(data.isAdmin ? 'admin_dashboard.php' : 'homepage.php');
                            }
                        })
                        .catch(error => console.error('Error checking session:', error));
                };
            }
        });
    </script>
</body>
</html>