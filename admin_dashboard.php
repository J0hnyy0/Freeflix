<?php
require_once 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle AJAX search request
if (isset($_GET['ajax_search'])) {
    $search = $_GET['ajax_search'];
    $stmt = $conn->prepare("SELECT id, username, email, registration_date FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY registration_date DESC");
    $search_param = "%$search%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($users);
    exit();
}

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = $search ? "WHERE username LIKE ? OR email LIKE ?" : '';
$search_param = $search ? "%$search%" : '';

// Define active section (default to dashboard)
$active_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Define movies from homepage.php (title and image only)
$movies = [
    ['title' => 'Moana 2', 'image' => 'moviepics/moana2.jpg'],
    ['title' => 'Sonic the Hedgehog 3', 'image' => 'moviepics/sonic.jpg'],
    ['title' => 'Red One', 'image' => 'moviepics/redone.jpg'],
    ['title' => 'Transformers One', 'image' => 'moviepics/transformers.jpg'],
    ['title' => 'Venom: The Last Dance', 'image' => 'moviepics/venom.jpg'],
    ['title' => 'Deadpool & Wolverine', 'image' => 'moviepics/deadpool.jpg'],
    ['title' => 'Despicable Me 4', 'image' => 'moviepics/despicable.jpg'],
    ['title' => 'Elemental', 'image' => 'moviepics/elemental.jpeg'],
    ['title' => 'The Wild Robot', 'image' => 'moviepics/thewildrobot.jpg'],
    ['title' => 'Inside Out 2', 'image' => 'moviepics/insideout2.jpg'],
    ['title' => 'Leo', 'image' => 'moviepics/leo.jpg'],
    ['title' => 'Turning Red', 'image' => 'moviepics/red.jpg'],
    ['title' => 'Dolittle', 'image' => 'moviepics/dolittle.jpg'],
    ['title' => 'Spellbound', 'image' => 'moviepics/spellbound.jpg'],
    ['title' => 'The Super Mario Bros', 'image' => 'moviepics/mario.jpg'],
    ['title' => 'Trolls Band Together', 'image' => 'moviepics/trolls.jpg'],
    ['title' => 'The Little Mermaid', 'image' => 'moviepics/mermaid.jpg'],
    ['title' => 'A Quiet Place: Day One', 'image' => 'moviepics/day one.jpg'],
    ['title' => 'Kung Fu Panda 4', 'image' => 'moviepics/kungfupanda4.jpg'],
    ['title' => 'Migration', 'image' => 'moviepics/migration.jpg']
];

// Check if watch_history table exists and get watch history counts
$movie_counts = [];
$total_watch_count = 0;
$user_count = 0;
$most_watched_movie = ['title' => 'N/A', 'count' => 0];

$table_check = $conn->query("SHOW TABLES LIKE 'watch_history'");
if ($table_check->num_rows > 0) {
    // Query to count watches per movie
    $watch_stmt = $conn->prepare("SELECT movie_title, COUNT(*) as watch_count FROM watch_history GROUP BY movie_title ORDER BY watch_count DESC");
    $watch_stmt->execute();
    $watch_result = $watch_stmt->get_result();
    while ($row = $watch_result->fetch_assoc()) {
        $movie_counts[$row['movie_title']] = $row['watch_count'];
        $total_watch_count += $row['watch_count'];
        // Determine the most watched movie
        if ($row['watch_count'] > $most_watched_movie['count']) {
            $most_watched_movie = [
                'title' => $row['movie_title'],
                'count' => $row['watch_count']
            ];
        }
    }
    // Initialize counts for all movies
    foreach ($movies as $movie) {
        $movie_counts[$movie['title']] = isset($movie_counts[$movie['title']]) ? $movie_counts[$movie['title']] : 0;
    }
} else {
    $error_message = "Watch history table does not exist. Please create the watch_history table.";
}

// Get total user count
$user_stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM users");
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_count = $user_result->fetch_assoc()['user_count'];

// Handle user creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?section=users&message=User created successfully" . ($search ? "&search=" . urlencode($search) : ""));
    } else {
        header("Location: admin_dashboard.php?section=users&error=Failed to create user" . ($search ? "&search=" . urlencode($search) : ""));
    }
    exit();
}

// Handle user edit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($_POST['password']) && $_POST['password'] !== $confirm_password) {
        header("Location: admin_dashboard.php?section=users&error=Passwords do not match" . ($search ? "&search=" . urlencode($search) : ""));
        exit();
    }

    if ($user_id != $_SESSION['user_id']) {
        if ($password) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?section=users&message=User updated successfully" . ($search ? "&search=" . urlencode($search) : ""));
        } else {
            header("Location: admin_dashboard.php?section=users&error=Failed to update user" . ($search ? "&search=" . urlencode($search) : ""));
        }
        exit();
    }
}

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        header("Location: admin_dashboard.php?section=users&message=User deleted successfully" . ($search ? "&search=" . urlencode($search) : ""));
        exit();
    }
}

// Get users from database
$stmt = $conn->prepare("SELECT id, username, email, registration_date FROM users $search_condition ORDER BY registration_date DESC");
if ($search) {
    $stmt->bind_param("ss", $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FreeFlix</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admind.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Freeflix Admin</h3>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php?section=dashboard" class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_dashboard.php?section=users" class="<?php echo $active_section == 'users' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_dashboard.php?section=watch_count" class="<?php echo $active_section == 'watch_count' ? 'active' : ''; ?>"><i class="fas fa-eye"></i> Watch Count</a></li>
            <li><a href="#" onclick="showLogoutPopup(); return false;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Admin Container -->
        <div class="admin-container">
            <?php if (isset($_GET['message'])): ?>
                <div class="message"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section <?php echo $active_section == 'dashboard' ? 'active' : ''; ?>">
                <div class="admin-title">
                    <h3>Dashboard</h3>
                </div>
                <div class="stats-container">
                    <div class="stat-card movies">
                        <h3>Total Movies</h3>
                        <div class="count"><?php echo count($movies); ?> Movies</div>
                        <a href="admin_dashboard.php?section=watch_count">View all movies <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-card users">
                        <h3>Total Users</h3>
                        <div class="count"><?php echo htmlspecialchars($user_count); ?> Users</div>
                        <a href="admin_dashboard.php?section=users">View all users <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-card watches">
                        <h3>Total Watches</h3>
                        <div class="count"><?php echo htmlspecialchars($total_watch_count); ?> Watches</div>
                        <a href="admin_dashboard.php?section=watch_count">View watch counts <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-card most-watched">
                        <h3>Most Watched Movie</h3>
                        <div class="count"><?php echo htmlspecialchars($most_watched_movie['title']); ?> (<?php echo $most_watched_movie['count']; ?>)</div>
                        <a href="admin_dashboard.php?section=watch_count">View watch counts <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="section <?php echo $active_section == 'users' ? 'active' : ''; ?>">
                <div class="admin-title">
                    <h3>Users List</h3>
                    <button class="create-user-btn" onclick="showCreatePopup()">Create New User</button>
                </div>

                <div class="search-container">
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Search by username or email..." 
                        class="search-input"
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                    <div id="search-loading" style="display: none; position: absolute; right: 50px; top: 50%; transform: translateY(-50%);">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>

                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>USERNAME</th>
                            <th>EMAIL</th>
                            <th>REGISTRATION DATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['registration_date']); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="edit-btn" onclick="showEditPopup(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>')">Edit</button>
                                        <button type="button" class="action-btn" onclick="showDeletePopup(<?php echo $user['id']; ?>)">Delete</button>
                                    <?php else: ?>
                                        <span style="color: #999;">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Watch Count Section -->
            <div id="watch-count-section" class="section <?php echo $active_section == 'watch_count' ? 'active' : ''; ?>">
                <div class="admin-title">
                    <h3>Movie Watch Counts</h3>
                </div>
                
                <table class="watch-table">
                    <thead>
                        <tr>
                            <th>MOVIE</th>
                            <th>WATCH COUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sorted_movies = $movies;
                        usort($sorted_movies, function($a, $b) use ($movie_counts) {
                            return $movie_counts[$b['title']] - $movie_counts[$a['title']];
                        });
                        
                        foreach ($sorted_movies as $movie): 
                        ?>
                            <tr>
                                <td class="movie-title">
                                    <img src="<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-thumb">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </td>
                                <td class="watch-count"><?php echo htmlspecialchars($movie_counts[$movie['title']]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Popups -->
    <div class="popup-overlay" id="createPopup">
        <div class="popup popup-white">
            <div class="popup-header">
                <h3>Create New User</h3>
                <button type="button" class="popup-close-btn" onclick="hideCreatePopup()">✖</button>
            </div>
            <form method="POST" class="popup-form">
                <input type="hidden" name="section" value="users">
                <label for="createUsername">Username</label>
                <input type="text" name="username" id="createUsername" required>
                <label for="createEmail">Email</label>
                <input type="email" name="email" id="createEmail" required>
                <label for="createPassword">Password</label>
                <input type="password" name="password" id="createPassword" required>
                <div class="popup-buttons">
                    <button type="submit" name="create_user" class="popup-btn popup-btn-ok">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="deletePopup">
        <div class="popup">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="popup-buttons">
                <button class="popup-btn popup-btn-ok" id="confirmDeleteBtn">OK</button>
                <button class="popup-btn popup-btn-cancel" onclick="hideDeletePopup()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="editPopup">
        <div class="popup popup-white">
            <div class="popup-header">
                <h3>Edit User</h3>
                <button type="button" class="popup-close-btn" onclick="hideEditPopup()">✖</button>
            </div>
            <form method="POST" class="popup-form">
                <input type="hidden" name="section" value="users">
                <input type="hidden" name="user_id" id="editUserId">
                <label for="editUsername">Username</label>
                <input type="text" name="username" id="editUsername" required>
                <label for="editEmail">Email</label>
                <input type="email" name="email" id="editEmail" required>
              
                <div class="popup-buttons">
                    <button type="submit" name="edit_user" class="popup-btn popup-btn-ok">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="logoutPopup">
        <div class="popup">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out?</p>
            <div class="popup-buttons">
                <button class="popup-btn popup-btn-ok" id="confirmLogoutBtn">OK</button>
                <button class="popup-btn popup-btn-cancel" onclick="hideLogoutPopup()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search-input');
        const tableBody = document.getElementById('user-table-body');
        const deletePopup = document.getElementById('deletePopup');
        const editPopup = document.getElementById('editPopup');
        const createPopup = document.getElementById('createPopup');
        const logoutPopup = document.getElementById('logoutPopup');
        let debounceTimer;
        let deleteForm = null;

        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => searchUsers(e.target.value), 300);
        });

        async function searchUsers(query) {
            try {
                if (!query) {
                    window.location.href = 'admin_dashboard.php?section=users';
                    return;
                }

                document.getElementById('search-loading').style.display = 'block';
                
                const response = await fetch(`admin_dashboard.php?ajax_search=${encodeURIComponent(query)}`);
                const users = await response.json();
                updateTable(users);
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                document.getElementById('search-loading').style.display = 'none';
            }
        }

        function updateTable(users) {
            tableBody.innerHTML = '';
            users.forEach((user, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${escapeHtml(user.username)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.registration_date)}</td>
                    <td>
                        ${user.id != <?php echo $_SESSION['user_id']; ?> ? `
                            <button type="button" class="edit-btn" onclick="showEditPopup(${user.id}, '${escapeHtml(user.username)}', '${escapeHtml(user.email)}')">Edit</button>
                            <button type="button" class="action-btn" onclick="showDeletePopup(${user.id})">Delete</button>
                        ` : '<span style="color: #999;">(You)</span>'}
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        function showCreatePopup() {
            createPopup.style.display = 'flex';
        }

        function hideCreatePopup() {
            createPopup.style.display = 'none';
            document.getElementById('createUsername').value = '';
            document.getElementById('createEmail').value = '';
            document.getElementById('createPassword').value = '';
        }

        function showDeletePopup(userId) {
            deleteForm = document.createElement('form');
            deleteForm.method = 'POST';
            deleteForm.innerHTML = `
                <input type="hidden" name="section" value="users">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="delete_user" value="1">
            `;
            deletePopup.style.display = 'flex';
            document.getElementById('confirmDeleteBtn').onclick = function() {
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            };
        }

        function hideDeletePopup() {
            deletePopup.style.display = 'none';
            deleteForm = null;
        }

        function showEditPopup(userId, username, email) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
         
            editPopup.style.display = 'flex';
        }

        function hideEditPopup() {
            editPopup.style.display = 'none';
            document.getElementById('editUserId').value = '';
            document.getElementById('editUsername').value = '';
            document.getElementById('editEmail').value = '';
           
        }

        function showLogoutPopup() {
            logoutPopup.style.display = 'flex';
            document.getElementById('confirmLogoutBtn').onclick = function() {
                window.location.href = 'logout.php';
            };
        }

        function hideLogoutPopup() {
            logoutPopup.style.display = 'none';
        }

        function escapeHtml(unsafe) {
            return unsafe
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>