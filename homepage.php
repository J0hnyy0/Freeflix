<?php
// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Require login to access this page
requireLogin();

// Get username from session
$username = $_SESSION['username'];

// Handle AJAX profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_PROFILE_UPDATE'])) {
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

// Logout function
if (isset($_GET['logout'])) {
    global $conn;

    // Clear session token in the database
    $stmt = $conn->prepare("UPDATE users SET session_token = NULL WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Destroy the session
    session_unset();
    session_destroy();
    header("Location: login.php");
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
    <title>FreeFlix - Movie Streaming</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <header>
        <div class="badge-container">
                <a href="#" id="userSidebarToggle" class="user-icon-link" title="User Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </a>
            <a href="homepage.php">
            <span class="free-badge">Free</span>
            <span class="flix-text">Flix</span>
            </a>
        </div>
        <nav>
            <a href="#" class="nav-link active">Home</a>
            <a href="history.php" class="nav-link">Watch History</a>
            <a href="#" id="logoutLink" class="nav-link logout-link">Logout</a>
            <div class="search-container">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="searchBar" placeholder="Search movies...">
                <button class="clear-search" id="clearSearch">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div id="searchResults" class="search-results"></div>
            </div>
        </nav>
    </header>

    <!-- User Sidebar Menu -->
    <div id="userSidebar" class="user-sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <svg class="user-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <h3><?php echo htmlspecialchars($username); ?></h3>
            </div>
            <button id="closeSidebar">×</button>
        </div>
        <div class="sidebar-content">
            <ul class="sidebar-menu">
                <li><a href="#" id="showProfileModal"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l-.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> Account Settings</a></li>
                <li><a href="history.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Watch History</a></li>
                <li class="divider"></li>
                <li><a href="#" id="sidebarLogout" class="logout-link"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Sidebar overlay background -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Profile Modal -->
    <div class="modal-overlay" id="profileModal">
        <div class="profile-modal">
            <button class="close-btn" id="closeProfileModal">×</button>
            <div class="profile-picture">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h1>
            <div class="profile-handle"><?php echo htmlspecialchars($user['email']); ?></div>
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
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModal">
        <div class="edit-profile-modal">
            <h3>Edit Profile</h3>
            <button class="close-btn" id="closeEditModal">×</button>
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

    <!-- Logout modal HTML -->
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

    <section class="top-ratings">
        <h2>Top Ratings</h2>
        <div class="featured-container">
            <div class="featured-main">
                <video class="featured-video" autoplay loop muted playsinline poster="moviepics/mermaidd.jpg" aria-label="Featured movie trailer">
                    <source src="movievids/thelittlemermaid.mp4" type="video/mp4">
                    <img src="moviepics/mermaidd.jpg" alt="The Little Mermaid">
                </video>
                <div class="featured-info">
                    <h3>The Little Mermaid</h3>
                    <p>The youngest of King Tritons daughters, and the most defiant, Ariel longs to find out more about the world beyond the sea, and while visiting the surface, falls for the dashing Prince Eric. With mermaids forbidden to interact with humans, Ariel makes a deal with the evil sea witch, Ursula, which gives her a chance to experience life on land, but ultimately places her life and her fathers crown in jeopardy.</p>
                    <button class="watch-now" onclick="redirectToMovie('The Little Mermaid')">▶ Watch now</button>
                </div>
            </div>
            <div class="featured-sidebar">
                <div class="sidebar-item">
                    <img src="moviepics/moana2.jpg" alt="Moana 2">
                    <div class="sidebar-info">
                        <h4>Moana 2</h4>
                        <p>After receiving an unexpected call from her wayfinding ancestors, Moana journeys alongside Maui and a new crew to the far seas of Oceania and into dangerous, long-lost waters for an adventure unlike anything she's ever faced.</p>
                        <button class="watch-now" onclick="redirectToMovie('Moana 2')">▶ Watch now</button>
                    </div>
                </div>
                <div class="sidebar-item">
                    <img src="moviepics/insideout2.jpg" alt="Inside Out 2">
                    <div class="sidebar-info">
                        <h4>Inside Out 2</h4>
                        <p>A sequel that features Riley entering puberty and experiencing brand new, more complex emotions as a result. As Riley tries to adapt to her teenage years, her old emotions try to adapt to the possibility of being replaced.</p>
                        <button class="watch-now" onclick="redirectToMovie('Inside Out 2')">▶ Watch now</button>
                    </div>
                </div>
                <div class="sidebar-item">
                    <img src="moviepics/day one.jpg" alt="A Quiet Place: Day One">
                    <div class="sidebar-info">
                        <h4>A Quiet Place: Day One</h4>
                        <p>As New York City is invaded by alien creatures who hunt by sound, a woman named Sammy fights to survive.</p>
                        <button class="watch-now" onclick="redirectToMovie('A Quiet Place: Day One')">▶ Watch now</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Bar (Only genre) -->
    <div class="filter-bar">
        <div class="filter-section">
            <label>Genre</label>
            <div class="filter-options" data-filter="genre">
                <button class="filter-btn selected" data-value="ALL">ALL</button>
                <button class="filter-btn" data-value="Animation">Animation</button>
                <button class="filter-btn" data-value="Action">Action</button>
                <button class="filter-btn" data-value="Adventure">Adventure</button>
                <button class="filter-btn" data-value="Comedy">Comedy</button>
                <button class="filter-btn" data-value="Fantasy">Fantasy</button>
            </div>
        </div>
    </div>

    <section class="movies">
        <h2>Movies</h2>
        <div class="movie-grid" id="movie-grid"></div>
    </section>

    <script>
// Redirect function for buttons
function redirectToMovie(movieTitle) {
    try {
        console.log('Redirecting to movie:', movieTitle);
        window.location.href = 'md.php?title=' + encodeURIComponent(movieTitle);
    } catch (error) {
        console.error('Error redirecting to movie:', error);
    }
}

// Main featured movies database with additional metadata
const mainFeaturedMovies = [
    {
        title: "Moana 2",
        image: "moviepics/moana2.jpg",
        video: "movievids/moana2.mp4",
        description: "Disney's animated sequel follows Moana, now a confident wayfinder, embarking on a daring journey to Oceania alongside her new little sister, Simea, and other unique community members. The film emphasizes themes of sisterhood, risk-taking, cultural heritage, and community.",
        category: "Family/Animated",
        genres: ["Animation", "Adventure", "Fantasy"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Sonic the Hedgehog 3",
        image: "moviepics/sonic.jpg",
        video: "movievids/sonic3.mp4",
        description: "This installment continues the adventures of Sonic, Tails, and Knuckles as they confront new challenges, including the emergence of Shadow the Hedgehog. The film introduces Keanu Reeves voicing the villain Shadow and features Jim Carrey returning as Dr. Robotnik.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure", "Animation"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Red One",
        image: "moviepics/redone.jpg",
        video: "movievids/redone.mp4",
        description: "After Santa Claus is kidnapped, the North Pole's Head of Security must team up with a notorious hacker in a globe-trotting, action-packed mission to save Christmas.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Transformers One",
        image: "moviepics/transformers.jpg",
        video: "movievids/transformersone.mp4",
        description: "Is a 2024 animated action-adventure film that tells the origin story of Optimus Prime and Megatron, exploring their friendship and the events that led to their rivalry on Cybertron.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure", "Animation"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Venom: The Last Dance",
        image: "moviepics/venom.jpg",
        video: "movievids/venomlastdance.mp4",
        description: "In the final film of the trilogy, Eddie Brock and Venom are on the run, hunted by both of their worlds. The duo faces a devastating decision that will conclude their journey together.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Deadpool & Wolverine",
        image: "moviepics/deadpool.jpg",
        video: "movievids/deadpoolwolverine.mp4",
        description: "In \"Deadpool & Wolverine,\" Wade Wilson, tired of his life as Deadpool, finds himself thrust back into action when he's recruited by the Time Variance Authority to save his universe, forcing him to team up with a reluctant Wolverine.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Despicable Me 4",
        image: "moviepics/despicable.jpg",
        video: "movievids/despicableme4.mp4",
        description: "Gru, Lucy, and their daughters welcome a new member to the family, Gru Jr., who is intent on tormenting his dad. Gru faces a new nemesis, Maxime Le Mal, and his girlfriend Valentina, forcing the family to go on the run.",
        category: "Family/Animated",
        genres: ["Animation", "Comedy"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Elemental",
        image: "moviepics/elemental.jpeg",
        video: "movievids/elemental.mp4",
        description: "A Disney and Pixar film, is a romantic comedy-drama set in Element City, where people representing fire, water, earth, and air coexist, following the story of Ember, a fiery young woman, and Wade, a water element, as they navigate their differences and discover their connection.",
        category: "Family/Animated",
        genres: ["Animation"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    },
    {
        title: "The Wild Robot",
        image: "moviepics/thewildrobot.jpg",
        video: "movievids/thewildrobot.mp4",
        description: "A heartfelt animated adventure about a shipwrecked robot, Roz, who must adapt to a wild island, befriend the animals, and become the adoptive mother of an orphaned gosling, Brightbill.",
        category: "Family/Animated",
        genres: ["Animation"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Inside Out 2",
        image: "moviepics/insideout2.jpg",
        video: "movievids/insideout2.mp4",
        description: "Follows teenage Riley as she navigates puberty and new emotions, including Anxiety, who threatens to take over her mind, while Joy, Sadness, Anger, Fear, and Disgust try to reclaim control.",
        category: "Family/Animated",
        genres: ["Animation"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Leo",
        image: "moviepics/leo.jpg",
        video: "movievids/leo.mp4",
        description: "A Netflix animated musical about a jaded, 74-year-old lizard named Leo, voiced by Adam Sandler, who, after learning he has only one year left to live, plans to escape his terrarium in a Florida classroom but instead gets caught up in his students' problems.",
        category: "Family/Animated",
        genres: ["Animation", "Comedy"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    },
    {
        title: "Turning Red",
        image: "moviepics/red.jpg",
        video: "movievids/turningred.mp4",
        description: "A coming-of-age animated film from Disney and Pixar, set in 2002 Toronto, following 13-year-old Mei Lee as she navigates adolescence and her relationship with her overprotective mother, while also dealing with the magical ability to transform into a giant red panda when overwhelmed with strong emotions.",
        category: "Family/Animated",
        genres: ["Animation"],
        country: "Canada",
        year: 2022,
        dubbing: "ALL"
    },
    {
        title: "Dolittle",
        image: "moviepics/dolittle.jpg",
        video: "movievids/dolittle.mp4",
        description: "A reclusive doctor who can talk to animals embarks on a magical journey, with the help of his animal companions, to find a cure for a gravely ill Queen Victoria, based on Hugh Lofting's popular children's book series.",
        category: "Comedy",
        genres: ["Comedy"],
        country: "United States",
        year: 2020,
        dubbing: "ALL"
    },
    {
        title: "Spellbound",
        image: "moviepics/spellbound.jpg",
        video: "movievids/spellbound.mp4",
        description: "In the magical kingdom of Lumbria, Princess Ellian seeks to break a spell that has transformed her parents into monsters. She embarks on a journey to find the Oracles of the Sun and Moon to restore her family.",
        category: "Drama/Fantasy",
        genres: ["Fantasy", "Adventure", "Animation"],
        country: "Other",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "The Super Mario Bros",
        image: "moviepics/mario.jpg",
        video: "movievids/supermariobros.mp4",
        description: "A Brooklyn plumber named Mario travels through the Mushroom Kingdom with a princess named Peach and an anthropomorphic mushroom named Toad to find Mario's brother, Luigi, and to save the world from a ruthless fire-breathing Koopa named Bowser.",
        category: "Family/Animated",
        genres: ["Animation", "Action"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    },
    {
        title: "Trolls Band Together",
        image: "moviepics/trolls.jpg",
        video: "movievids/trollsbandtogether.mp4",
        description: "Poppy and Branch discover Branch's secret past as a boy band member of BroZone, and when his brother Floyd is kidnapped, they embark on a journey to reunite the other brothers and rescue him.",
        category: "Family/Animated",
        genres: ["Animation", "Adventure"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    },
    {
        title: "The Little Mermaid",
        image: "moviepics/mermaid.jpg",
        video: "movievids/thelittlemermaid.mp4",
        description: "A young mermaid princess fascinated by the human world, who makes a deal with a sea witch to become human and win the love of a prince, facing a time limit and potentially dire consequences.",
        category: "Family/Animated",
        genres: ["Animation", "Fantasy"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    },
    {
        title: "A Quiet Place: Day One",
        image: "moviepics/day one.jpg",
        video: "movievids/aquietplacedayone.mp4",
        description: "A prequel to the \"A Quiet Place\" franchise, set during the initial stages of the alien invasion in New York City, where a terminally ill woman named Samira must survive with her cat and a new friend, Eric, as the world descends into silence.",
        category: "Action/Adventure",
        genres: ["Action", "Adventure"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Kung Fu Panda 4",
        image: "moviepics/kungfupanda4.jpg",
        video: "movievids/kungfupanda4.mp4",
        description: "Po, now the spiritual leader of the Valley of Peace, must find and train a new Dragon Warrior to replace him, while facing a new threat: a shape-shifting sorceress, the Chameleon, who seeks to summon past villains.",
        category: "Family/Animated",
        genres: ["Animation", "Adventure", "Action"],
        country: "United States",
        year: 2024,
        dubbing: "ALL"
    },
    {
        title: "Migration",
        image: "moviepics/migration.jpg",
        video: "movievids/migration.mp4",
        description: "A 2023 animated family comedy-adventure film from Illumination Studios about a family of ducks who embark on their first-ever migration from their New England pond to Jamaica, encountering various adventures and challenges along the way.",
        category: "Family/Animated",
        genres: ["Animation", "Comedy", "Adventure"],
        country: "United States",
        year: 2023,
        dubbing: "ALL"
    }
];

// Sidebar movies database
const sidebarMovies = [
    {
        title: "Moana 2",
        image: "moviepics/moana2.jpg",
        description: "After receiving an unexpected call from her wayfinding ancestors, Moana must journey to the far seas of Oceania and into dangerous, long-lost waters for an adventure unlike anything she's ever faced."
    },
    {
        title: "Sonic the Hedgehog 3",
        image: "moviepics/sonic.jpg",
        description: "Sonic, Knuckles, and Tails reunite to combat a new formidable foe, Shadow, a mysterious hedgehog with powers unlike anything they've seen before."
    },
    {
        title: "Red One",
        image: "moviepics/redone.jpg",
        description: "Cal and Jack must work together to track down Santa and prevent a holiday disaster."
    },
    {
        title: "Transformers One",
        image: "moviepics/transformers.jpg",
        description: "The untold origin story of Optimus Prime and Megatron, better known as sworn enemies, but once were friends bonded like brothers who changed the fate of Cybertron forever."
    },
    {
        title: "Venom: The Last Dance",
        image: "moviepics/venom.jpg",
        description: "Eddie Brock and Venom must make a devastating decision as they're pursued by a mysterious military man and alien monsters from Venom's home world."
    },
    {
        title: "Deadpool & Wolverine",
        image: "moviepics/deadpool.jpg",
        description: "Deadpool finds himself teaming up with a reluctant Wolverine for an adventure that will change both of their lives forever."
    },
    {
        title: "Despicable Me 4",
        image: "moviepics/despicable.jpg",
        description: "Gru, Lucy, and their daughters welcome a new family member, Gru Jr., while facing a new nemesis, Maxime Le Mal, and his girlfriend Valentina, forcing the family to go on the run."
    },
    {
        title: "Elemental",
        image: "moviepics/elemental.jpeg",
        description: "In a city where fire, water, land, and air residents live together, a fiery young woman and a go-with-the-flow guy discover something elemental: how much they actually have in common."
    },
    {
        title: "The Wild Robot",
        image: "moviepics/thewildrobot.jpg",
        description: "After a shipwreck, an intelligent robot called Roz is stranded on an uninhabited island. To survive the harsh environment, Roz bonds with the island's animals and cares for an orphaned baby goose."
    },
    {
        title: "Inside Out 2",
        image: "moviepics/insideout2.jpg",
        description: "Joy, Sadness, Anger, Fear and Disgust have been running a successful operation by all accounts. However, when Anxiety shows up, they aren't sure how to feel."
    },
    {
        title: "Leo",
        image: "moviepics/leo.jpg",
        description: "A 74-year-old lizard named Leo and his turtle friend decide to escape from the terrarium of a Florida school classroom where they have been living for decades."
    },
    {
        title: "Turning Red",
        image: "moviepics/red.jpg",
        description: "A thirteen-year-old girl named Mei Lee is torn between staying her mother's dutiful daughter and the changes of adolescence. Whenever she gets overly excited Mei transforms into a giant red panda."
    },
    {
        title: "Dolittle",
        image: "moviepics/dolittle.jpg",
        description: "A physician who can talk to animals embarks on an adventure to find a legendary island with a young apprentice and a crew of strange pets."
    },
    {
        title: "Spellbound",
        image: "moviepics/spellbound.jpg",
        description: "Ellian is a tenacious princess who must go on a daring quest to save her family and kingdom after a mysterious spell transforms her parents, the King and Queen of Lumbria, into monsters."
    },
    {
        title: "The Super Mario Bros",
        image: "moviepics/mario.jpg",
        description: "Brooklyn plumbers Mario and Luigi are warped to the magical Mushroom Kingdom, and Mario must team up with Princess Peach, Toad, and Donkey Kong to save Luigi from the evil Bowser."
    },
    {
        title: "Trolls Band Together",
        image: "moviepics/trolls.jpg",
        description: "Poppy discovers that Branch was once part of the boy band 'BroZone' with his brothers, Floyd, John Dory, Spruce and Clay. When Floyd is kidnapped, Branch and Poppy embark on a journey to reunite his two other brothers and rescue Floyd."
    },
    {
        title: "The Little Mermaid",
        image: "moviepics/mermaid.jpg",
        description: "A young mermaid makes a deal with a sea witch to trade her beautiful voice for human legs so she can discover the world above water and impress a prince."
    },
    {
        title: "A Quiet Place: Day One",
        image: "moviepics/day one.jpg",
        description: "A young woman named Sam finds herself trapped in New York City during the early stages of an invasion by alien creatures with ultra-sensitive hearing."
    },
    {
        title: "Kung Fu Panda 4",
        image: "moviepics/kungfupanda4.jpg",
        description: "After Po is tapped to become the Spiritual Leader of the Valley of Peace, he needs to find and train a new Dragon Warrior, while a wicked sorceress plans to re-summon all the master villains whom Po has vanquished to the spirit realm."
    },
    {
        title: "Migration",
        image: "moviepics/migration.jpg",
        description: "The story follows a family of mallards who try to convince their overprotective father to go on a vacation of a lifetime and attempt to migrate from New England, through New York City, to Jamaica."
    }
];

// Search movies database
const searchMovies = [
    { title: "Moana 2", img: "moviepics/moana2.jpg" },
    { title: "Sonic the Hedgehog 3", img: "moviepics/sonic.jpg" },
    { title: "Red One", img: "moviepics/redone.jpg" },
    { title: "Transformers One", img: "moviepics/transformers.jpg" },
    { title: "Venom: The Last Dance", img: "moviepics/venom.jpg" },
    { title: "Deadpool & Wolverine", img: "moviepics/deadpool.jpg" },
    { title: "Despicable Me 4", img: "moviepics/despicable.jpg" },
    { title: "Elemental", img: "moviepics/elemental.jpeg" },
    { title: "The Wild Robot", img: "moviepics/thewildrobot.jpg" },
    { title: "Inside Out 2", img: "moviepics/insideout2.jpg" },
    { title: "Leo", img: "moviepics/leo.jpg" },
    { title: "Turning Red", img: "moviepics/red.jpg" },
    { title: "Dolittle", img: "moviepics/dolittle.jpg" },
    { title: "Spellbound", img: "moviepics/spellbound.jpg" },
    { title: "The Super Mario Bros", img: "moviepics/mario.jpg" },
    { title: "Trolls Band Together", img: "moviepics/trolls.jpg" },
    { title: "The Little Mermaid", img: "moviepics/mermaid.jpg" },
    { title: "A Quiet Place: Day One", img: "moviepics/day one.jpg" },
    { title: "Kung Fu Panda 4", img: "moviepics/kungfupanda4.jpg" },
    { title: "Migration", img: "moviepics/migration.jpg" }
];

function getRandomUniqueItems(array, count) {
    const shuffled = [...array].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, count);
}

function updateMainFeaturedMovie() {
    try {
        const randomIndex = Math.floor(Math.random() * mainFeaturedMovies.length);
        const selectedMovie = mainFeaturedMovies[randomIndex];
        const featuredMain = document.querySelector('.featured-main');
        if (featuredMain) {
            const video = featuredMain.querySelector('.featured-video');
            const source = video.querySelector('source');
            const fallbackImg = video.querySelector('img');
            source.src = selectedMovie.video;
            video.poster = selectedMovie.image;
            fallbackImg.src = selectedMovie.image;
            fallbackImg.alt = selectedMovie.title;
            video.load();
            video.setAttribute('aria-label', `Featured movie trailer: ${selectedMovie.title}`);
            featuredMain.querySelector('.featured-info h3').textContent = selectedMovie.title;
            featuredMain.querySelector('.featured-info p').textContent = selectedMovie.description;
            featuredMain.querySelector('.watch-now').setAttribute('onclick', `redirectToMovie('${selectedMovie.title.replace(/'/g, "\\'")}')`);
        }
    } catch (error) {
        console.error('Error updating main featured movie:', error);
    }
}

function updateSidebarMovies() {
    try {
        const selectedMovies = getRandomUniqueItems(sidebarMovies, 3);
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        for (let i = 0; i < Math.min(sidebarItems.length, selectedMovies.length); i++) {
            const movie = selectedMovies[i];
            sidebarItems[i].querySelector('img').src = movie.image;
            sidebarItems[i].querySelector('img').alt = movie.title;
            sidebarItems[i].querySelector('h4').textContent = movie.title;
            sidebarItems[i].querySelector('p').textContent = movie.description;
            sidebarItems[i].querySelector('.watch-now').setAttribute('onclick', `redirectToMovie('${movie.title.replace(/'/g, "\\'")}')`);
        }
    } catch (error) {
        console.error('Error updating sidebar movies:', error);
    }
}

// Filter state (only genre)
let currentGenre = "ALL";

function applyFilters() {
    let filteredMovies = [...mainFeaturedMovies];

    // Apply genre filter
    if (currentGenre !== "ALL") {
        filteredMovies = filteredMovies.filter(movie => movie.genres.includes(currentGenre));
    }

    return filteredMovies;
}

function updateMovies() {
    try {
        const filteredMovies = applyFilters();
        const movieGrid = document.getElementById('movie-grid');
        movieGrid.innerHTML = filteredMovies.map(movie => `
            <div class="movie-card">
                <img src="${movie.image}" alt="${movie.title}">
                <h4>${movie.title}</h4>
                <button onclick="redirectToMovie('${movie.title.replace(/'/g, "\\'")}')">▶ Watch now</button>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error updating movies:', error);
    }
}

function updateAllMovies() {
    updateMainFeaturedMovie();
    updateSidebarMovies();
    updateMovies();
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        console.log('DOM loaded, initializing...');
        updateAllMovies();

        // Filter functionality (only genre)
        const genreFilterSection = document.querySelector('.filter-options[data-filter="genre"]');
        genreFilterSection.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove selected class from all buttons in this section
                genreFilterSection.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('selected'));
                // Add selected class to clicked button
                btn.classList.add('selected');
                // Update filter state
                currentGenre = btn.dataset.value;
                // Reapply filters and update movies
                updateMovies();
            });
        });

        // Search functionality
        document.getElementById("searchBar").addEventListener("input", function() {
            try {
                const query = this.value.trim().toLowerCase();
                const resultsContainer = document.getElementById("searchResults");

                if (query === '') {
                    resultsContainer.style.display = 'none';
                    return;
                }

                const filteredMovies = searchMovies.filter(movie => movie.title.toLowerCase().includes(query));
                resultsContainer.innerHTML = filteredMovies.map(movie => `
                    <div class="search-item" onclick="redirectToMovie('${movie.title.replace(/'/g, "\\'")}')">
                        <img src="${movie.img}" alt="${movie.title}">
                        <h4>${movie.title}</h4>
                    </div>
                `).join('');
                resultsContainer.style.display = filteredMovies.length ? 'block' : 'none';
            } catch (error) {
                console.error('Error in search functionality:', error);
            }
        });

        // Hide search results when clicking outside
        document.addEventListener("click", function(event) {
            try {
                if (!event.target.closest(".search-container")) {
                    document.getElementById("searchResults").style.display = "none";
                }
            } catch (error) {
                console.error('Error hiding search results:', error);
            }
        });

        // Sidebar functionality
        const userSidebarToggle = document.getElementById('userSidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const userSidebar = document.getElementById('userSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        userSidebarToggle.addEventListener('click', function(e) {
            try {
                console.log('Sidebar toggle clicked');
                e.preventDefault();
                userSidebar.classList.add('open');
                sidebarOverlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error opening sidebar:', error);
            }
        });

        function closeSidebarMenu() {
            try {
                console.log('Closing sidebar');
                userSidebar.classList.remove('open');
                sidebarOverlay.style.display = 'none';
                document.body.style.overflow = '';
            } catch (error) {
                console.error('Error closing sidebar:', error);
            }
        }

        closeSidebar.addEventListener('click', closeSidebarMenu);
        sidebarOverlay.addEventListener('click', closeSidebarMenu);

        // Profile Modal functionality
        const showProfileModal = document.getElementById('showProfileModal');
        const profileModal = document.getElementById('profileModal');
        const closeProfileModal = document.getElementById('closeProfileModal');

        showProfileModal.addEventListener('click', (e) => {
            e.preventDefault();
            closeSidebarMenu();
            profileModal.style.display = 'flex';
        });

        closeProfileModal.addEventListener('click', () => {
            profileModal.style.display = 'none';
        });

        profileModal.addEventListener('click', (event) => {
            if (event.target === profileModal) {
                profileModal.style.display = 'none';
            }
        });

        // Edit Profile Modal functionality
        const editProfileBtn = document.getElementById('editProfileBtn');
        const editProfileModal = document.getElementById('editProfileModal');
        const closeEditModal = document.getElementById('closeEditModal');

        editProfileBtn.addEventListener('click', () => {
            profileModal.style.display = 'none';
            editProfileModal.style.display = 'flex';
        });

        closeEditModal.addEventListener('click', () => {
            editProfileModal.style.display = 'none';
            resetEditForm();
        });

        editProfileModal.addEventListener('click', (event) => {
            if (event.target === editProfileModal) {
                editProfileModal.style.display = 'none';
                resetEditForm();
            }
        });

        // Edit Profile Form Handling
        const submitBtn = document.getElementById('submitBtn');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const successMessage = document.getElementById('successMessage');
        const usernameError = document.getElementById('usernameError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');

        function resetEditForm() {
            usernameError.style.display = 'none';
            emailError.style.display = 'none';
            passwordError.style.display = 'none';
            confirmPasswordError.style.display = 'none';
            successMessage.style.display = 'none';
            passwordInput.value = '';
            confirmPasswordInput.value = '';
            submitBtn.disabled = false;
        }

        function validateForm() {
            let isValid = true;
            resetEditForm();

            // Validate username
            if (!usernameInput.value.trim()) {
                usernameError.textContent = 'Username is required.';
                usernameError.style.display = 'block';
                isValid = false;
            } else if (usernameInput.value.trim().length < 3) {
                usernameError.textContent = 'Username must be at least 3 characters.';
                usernameError.style.display = 'block';
                isValid = false;
            }

            // Validate email
            if (!emailInput.value.trim()) {
                emailError.textContent = 'Email is required.';
                emailError.style.display = 'block';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
                emailError.textContent = 'Invalid email format.';
                emailError.style.display = 'block';
                isValid = false;
            }

            // Validate password (if provided)
            if (passwordInput.value) {
                if (passwordInput.value.length < 8) {
                    passwordError.textContent = 'Password must be at least 8 characters.';
                    passwordError.style.display = 'block';
                    isValid = false;
                }
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordError.textContent = 'Passwords do not match.';
                    confirmPasswordError.style.display = 'block';
                    isValid = false;
                }
            }

            return isValid;
        }

        submitBtn.addEventListener('click', async () => {
            if (!validateForm()) return;

            submitBtn.disabled = true;
            const formData = {
                username: usernameInput.value.trim(),
                email: emailInput.value.trim(),
                password: passwordInput.value,
                confirmPassword: confirmPasswordInput.value
            };

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Profile-Update': 'true'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    successMessage.textContent = result.message;
                    successMessage.style.display = 'block';

                    // Update username in sidebar and profile modal
                    document.querySelector('.sidebar-header h3').textContent = result.newUsername;
                    document.querySelector('.profile-name').textContent = result.newUsername;
                    document.querySelector('.profile-handle').textContent = '@' + result.newUsername.toLowerCase();

                    setTimeout(() => {
                        editProfileModal.style.display = 'none';
                        resetEditForm();
                    }, 1500);
                } else {
                    if (result.message.includes('Username')) {
                        usernameError.textContent = result.message;
                        usernameError.style.display = 'block';
                    } else if (result.message.includes('Email')) {
                        emailError.textContent = result.message;
                        emailError.style.display = 'block';
                    } else {
                        alert(result.message);
                    }
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                alert('An error occurred while updating your profile.');
                submitBtn.disabled = false;
            }
        });

        // Logout functionality
        const logoutLink = document.getElementById('logoutLink');
        const sidebarLogout = document.getElementById('sidebarLogout');
        const logoutModal = document.getElementById('logoutModal');
        const confirmLogout = document.getElementById('confirmLogout');
        const cancelLogout = document.getElementById('cancelLogout');

        function openLogoutModal() {
            logoutModal.style.display = 'flex';
            closeSidebarMenu();
        }

        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            openLogoutModal();
        });

        sidebarLogout.addEventListener('click', (e) => {
            e.preventDefault();
            openLogoutModal();
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

        // Clear search functionality
        const searchBar = document.getElementById('searchBar');
        const clearSearchBtn = document.getElementById('clearSearch');

        searchBar.addEventListener('input', () => {
            clearSearchBtn.style.display = searchBar.value ? 'block' : 'none';
        });

        clearSearchBtn.addEventListener('click', () => {
            searchBar.value = '';
            document.getElementById('searchResults').style.display = 'none';
            clearSearchBtn.style.display = 'none';
            searchBar.focus();
        });

    } catch (error) {
        console.error('Error during DOMContentLoaded:', error);
    }
});
</script>
</body>
</html>