<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';

// Ensure user is logged in
requireLogin();

$username = $_SESSION['username'];

// Handle AJAX profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $newUsername = trim($input['username'] ?? '');
        $newEmail = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $newPassword = $input['password'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';

        // Validate inputs
        if (!$newUsername) {
            throw new Exception('Username is required.');
        }
        if (strlen($newUsername) < 3) {
            throw new Exception('Username must be at least 3 characters.');
        }
        if (!$newEmail) {
            throw new Exception('Email is required.');
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }
        if ($newPassword && $newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match.');
        }
        if ($newPassword && strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters.');
        }

        // Check if username is already taken (excluding current user)
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? AND username != ?");
        $stmt->bind_param("ss", $newUsername, $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Username is already taken.');
        }
        $stmt->close();

        // Prepare update query
        $query = "UPDATE users SET username = ?, email = ?";
        $params = [$newUsername, $newEmail];
        $types = "ss";

        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashedPassword;
            $types .= "s";
        }

        $query .= " WHERE username = ?";
        $params[] = $username;
        $types .= "s";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Update session username if changed
            $_SESSION['username'] = $newUsername;
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully!';
            $response['newUsername'] = $newUsername;
        } else {
            throw new Exception('Failed to update profile.');
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeFlix - My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', Arial, sans-serif;
        }

        body {
            background-color:rgb(0, 0, 0);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center; 
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1rem;
            background:rgb(0, 0, 0);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .badge-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .free-badge {
            background: #F5C51C;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
        }

        .flix-text {
            color: #F5C51C;
            font-weight: 600;
            font-size: 18px;
        }

        a {
            text-decoration: none;
        }

        .nav-link {
            color: #fff;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-bottom: 2px solid transparent;
            transition: border-bottom 0.3s, color 0.3s;
        }

        .nav-link:hover {
            border-bottom: 2px solid #F5C51C;
        }

        .profile-container {
            max-width: 600px;
            width: 100%;
            text-align: center;
            padding: 1rem;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            background: #333;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #F5C51C;
            overflow: hidden;
        }

        .profile-picture svg {
            width: 60px;
            height: 60px;
            color: #F5C51C;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .profile-handle {
            font-size: 1rem;
            color: #A9A9A9;
            margin-bottom: 1.5rem;
        }

        .profile-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .action-btn {
            background: #3A3F44;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: #4A4F54;
        }

        .action-btn svg {
            width: 16px;
            height: 16px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal {
            background: linear-gradient(135deg, #1a1a1a, #333);
            border-radius: 10px;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1);
            border: 1px solid #444;
            position: relative;
            text-align: center;
        }

        .modal h3 {
            color: #F5C51C;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-family: "Roboto", sans-serif;
        }

        .modal .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #999;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal .close-btn:hover {
            color: #F5C51C;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #ccc;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #2E2E2E;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #F5C51C;
            box-shadow: 0 0 5px rgba(245, 197, 28, 0.3);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: none;
            animation: fadeIn 0.3s;
        }

        .success-message {
            color: #4CAF50;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
            animation: fadeIn 0.3s;
        }

        .submit-btn {
            background: #F5C51C;
            color: #000;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #F7CE3F;
        }

        .submit-btn:disabled {
            background: #999;
            cursor: not-allowed;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .modal-buttons button {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .modal-buttons .confirm-btn {
            background: #F5C51C;
            color: #000;
        }

        .modal-buttons .confirm-btn:hover {
            background: #F7CE3F;
        }

        .modal-buttons .cancel-btn {
            background: #444;
            color: #fff;
        }

        .modal-buttons .cancel-btn:hover {
            background: #555;
        }

        .logout-icon {
            width: 24px;
            height: 24px;
            margin: 0 auto 1rem;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media screen and (max-width: 768px) {
            .profile-container {
                padding: 1rem;
            }

            .profile-picture {
                width: 100px;
                height: 100px;
            }

            .profile-picture svg {
                width: 50px;
                height: 50px;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .profile-handle {
                font-size: 0.9rem;
            }

            header {
                flex-direction: row;
                align-items: center;
                padding: 0.5rem;
            }

            nav {
                display: flex;
                gap: 10px;
            }

            .nav-link {
                padding: 5px 8px;
                font-size: 0.8rem;
            }
        }

        @media screen and (max-width: 480px) {
            .profile-picture {
                width: 80px;
                height: 80px;
            }

            .profile-picture svg {
                width: 40px;
                height: 40px;
            }

            .profile-name {
                font-size: 1.3rem;
            }

            .profile-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .action-btn {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="badge-container">
            <a href="homepage.php">
                <span class="free-badge">Free</span>
                <span class="flix-text">Flix</span>
            </a>
        </div>
        <nav>
            <a href="homepage.php" class="nav-link">Home</a>
            <a href="history.php" class="nav-link">Watch History</a>
            <a href="#" id="logoutLink" class="nav-link">Logout</a>
        </nav>
    </header>

    <div class="modal-overlay" id="logoutModal">
        <div class="modal">
            <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="#F5C51C" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            <h3>Confirm Logout</h3>
            <p>Are you sure you'd like to log out? You'll need to sign in again to continue.</p>
            <div class="modal-buttons">
                <button class="confirm-btn" id="confirmLogout">OK</button>
                <button class="cancel-btn" id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="editProfileModal">
        <div class="modal">
            <button class="close-btn" id="closeEditModal">×</button>
            <h3>Edit Profile</h3>
            <div class="success-message" id="successMessage"></div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="Enter your username">
                <div class="error-message" id="usernameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Enter your email">
                <div class="error-message" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" placeholder="Enter new password (optional)">
                <div class="error-message" id="passwordError"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" placeholder="Confirm new password">
                <div class="error-message" id="confirmPasswordError"></div>
            </div>

            <button class="submit-btn" id="submitBtn">Update Profile</button>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-picture">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <h1 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h1>
        <div class="profile-handle">@<?php echo htmlspecialchars(strtolower($user['username'])); ?></div>
        <div class="profile-actions">
            <button class="action-btn" id="editProfileBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit Profile
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Edit Profile Modal
            const editProfileBtn = document.getElementById('editProfileBtn');
            const editProfileModal = document.getElementById('editProfileModal');
            const closeEditModal = document.getElementById('closeEditModal');

            editProfileBtn.addEventListener('click', () => {
                editProfileModal.style.display = 'flex';
            });

            closeEditModal.addEventListener('click', () => {
                editProfileModal.style.display = 'none';
            });

            editProfileModal.addEventListener('click', (event) => {
                if (event.target === editProfileModal) {
                    editProfileModal.style.display = 'none';
                }
            });

            // Profile update functionality
            const usernameInput = document.getElementById('username');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const submitBtn = document.getElementById('submitBtn');
            const usernameError = document.getElementById('usernameError');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            const successMessage = document.getElementById('successMessage');

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function showError(element, message) {
                element.style.display = 'block';
                element.textContent = message;
            }

            function clearErrors() {
                usernameError.style.display = 'none';
                emailError.style.display = 'none';
                passwordError.style.display = 'none';
                confirmPasswordError.style.display = 'none';
                successMessage.style.display = 'none';
            }

            submitBtn.addEventListener('click', async () => {
                clearErrors();
                let isValid = true;

                // Client-side validation
                if (!usernameInput.value) {
                    showError(usernameError, 'Username is required.');
                    isValid = false;
                } else if (usernameInput.value.length < 3) {
                    showError(usernameError, 'Username must be at least 3 characters.');
                    isValid = false;
                }

                if (!emailInput.value) {
                    showError(emailError, 'Email is required.');
                    isValid = false;
                } else if (!validateEmail(emailInput.value)) {
                    showError(emailError, 'Invalid email format.');
                    isValid = false;
                }

                if (passwordInput.value && passwordInput.value.length < 8) {
                    showError(passwordError, 'Password must be at least 8 characters.');
                    isValid = false;
                }

                if (passwordInput.value !== confirmPasswordInput.value) {
                    showError(confirmPasswordError, 'Passwords do not match.');
                    isValid = false;
                }

                if (!isValid) return;

                submitBtn.disabled = true;

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            username: usernameInput.value,
                            email: emailInput.value,
                            password: passwordInput.value,
                            confirmPassword: confirmPasswordInput.value
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        successMessage.style.display = 'block';
                        successMessage.textContent = result.message;
                        passwordInput.value = '';
                        confirmPasswordInput.value = '';
                        // Update displayed username and handle
                        document.querySelector('.profile-name').textContent = result.newUsername;
                        document.querySelector('.profile-handle').textContent = '@' + result.newUsername.toLowerCase();
                        setTimeout(() => {
                            editProfileModal.style.display = 'none';
                        }, 1000);
                    } else {
                        if (result.message.includes('Username')) {
                            showError(usernameError, result.message);
                        } else {
                            showError(emailError, result.message);
                        }
                    }
                } catch (error) {
                    showError(emailError, 'An error occurred. Please try again.');
                    console.error('Update profile error:', error);
                } finally {
                    submitBtn.disabled = false;
                }
            });

            // Logout modal functionality
            const logoutLink = document.getElementById('logoutLink');
            const logoutModal = document.getElementById('logoutModal');
            const confirmLogout = document.getElementById('confirmLogout');
            const cancelLogout = document.getElementById('cancelLogout');

            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                logoutModal.style.display = 'flex';
            });

            confirmLogout.addEventListener('click', () => {
                window.location.href = 'homepage.php?logout=true';
            });

            cancelLogout.addEventListener('click', () => {
                logoutModal.style.display = 'none';
            });

            logoutModal.addEventListener('click', (event) => {
                if (event.target === logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>