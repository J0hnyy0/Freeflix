<?php
session_start();
require 'config.php';

// Require user to be logged in
requireLogin();

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch watch history from the database
$stmt = $conn->prepare("SELECT id, movie_title, watch_date FROM watch_history WHERE user_id = ? ORDER BY watch_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle "Remove" button
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $delete_stmt = $conn->prepare("DELETE FROM watch_history WHERE id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $delete_id, $user_id);
        $delete_stmt->execute();
        header("Location: history.php");
        exit;
    }

    // Handle "Watch Again" button
    if (isset($_POST['watch_title'])) {
        $watch_title = $_POST['watch_title'];
        header("Location: movie.php?title=" . urlencode($watch_title));
        exit;
    }

    // Handle "Clear All History" button
    if (isset($_POST['clear_all'])) {
        $clear_stmt = $conn->prepare("DELETE FROM watch_history WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        if ($clear_stmt->execute()) {
            header("Location: history.php");
            exit;
        } else {
            error_log("Failed to clear watch history for user_id: $user_id");
            echo "Error clearing history. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch History</title>
    <link rel="stylesheet" href="css/history.css">
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
        <a href="homepage.php">Home</a>
        <a href="history.php" class="active">Watch History</a>
        <a href="#" id="logoutLink">Logout</a>
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

<!-- Logout modal -->
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

<!-- Clear history modal -->
<div class="modal-overlay" id="clearHistoryModal">
    <div class="modal">
        <svg class="clear-icon" viewBox="0 0 24 24" fill="none" stroke="#F5C51C" stroke-width="2">
            <path d="M3 6h18M6 6V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2m-2 0v14a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6h8zM10 11v6M14 11v6"/>
        </svg>
        <h3>Confirm Clear History</h3>
        <p>Are you sure you'd like to clear all your watch history? This action cannot be undone.</p>
        <div class="modal-buttons">
            <button class="confirm-btn" id="confirmClearHistory">OK</button>
            <button class="cancel-btn" id="cancelClearHistory">Cancel</button>
        </div>
    </div>
</div>

<!-- Remove history item modal -->
<div class="modal-overlay" id="removeHistoryModal">
    <div class="modal">
        <svg class="remove-icon" viewBox="0 0 24 24" fill="none" stroke="#F5C51C" stroke-width="2">
            <path d="M6 6h12M6 6V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2m-2 0v14a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6h8zM10 11v6M14 11v6"/>
        </svg>
        <h3>Confirm Remove</h3>
        <p>Are you sure you'd like to remove this movie from your watch history?</p>
        <div class="modal-buttons">
            <button class="confirm-btn" id="confirmRemoveHistory">OK</button>
            <button class="cancel-btn" id="cancelRemoveHistory">Cancel</button>
        </div>
    </div>
</div>

<div class="container">
    <div class="page-title">
        <h2>Watch History</h2>
        <div class="history-actions">
            <form method="post" id="clearHistoryForm">
                <input type="hidden" name="clear_all" value="1">
                <button type="button" id="clearHistoryBtn" class="btn btn-clear">Clear All History</button>
            </form>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <div class="history-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                $movieTitle = htmlspecialchars($row['movie_title']);
                $watchedAt = htmlspecialchars($row['watch_date']);
                
                // Default image path
                $imagePath = "moviepics/default.jpg";
                
                // Movies array for images
                $moviesArray = [
                    ["title" => "Moana 2", "img" => "moviepics/moana2.jpg"],
                    ["title" => "Sonic the Hedgehog 3", "img" => "moviepics/sonic.jpg"],
                    ["title" => "Red One", "img" => "moviepics/redone.jpg"],
                    ["title" => "Transformers One", "img" => "moviepics/transformers.jpg"],
                    ["title" => "Venom: The Last Dance", "img" => "moviepics/venom.jpg"],
                    ["title" => "Deadpool & Wolverine", "img" => "moviepics/deadpool.jpg"],
                    ["title" => "Despicable Me 4", "img" => "moviepics/despicable.jpg"],
                    ["title" => "Elemental", "img" => "moviepics/elemental.jpeg"],
                    ["title" => "The Wild Robot", "img" => "moviepics/thewildrobot.jpg"],
                    ["title" => "Inside Out 2", "img" => "moviepics/insideout2.jpg"],
                    ["title" => "Leo", "img" => "moviepics/leo.jpg"],
                    ["title" => "Turning Red", "img" => "moviepics/red.jpg"],
                    ["title" => "Dolittle", "img" => "moviepics/dolittle.jpg"],
                    ["title" => "Spellbound", "img" => "moviepics/spellbound.jpg"],
                    ["title" => "The Super Mario Bros", "img" => "moviepics/mario.jpg"],
                    ["title" => "Trolls Band Together", "img" => "moviepics/trolls.jpg"],
                    ["title" => "The Little Mermaid", "img" => "moviepics/mermaid.jpg"],
                    ["title" => "A Quiet Place: Day One", "img" => "moviepics/day one.jpg"],
                    ["title" => "Kung Fu Panda 4", "img" => "moviepics/kungfupanda4.jpg"],
                    ["title" => "Migration", "img" => "moviepics/migration.jpg"],
                ];
                
                // Find the matching movie image
                foreach ($moviesArray as $movie) {
                    if ($movie["title"] == $row['movie_title']) {
                        $imagePath = $movie["img"];
                        break;
                    }
                }
            ?>
            
            <div class="history-item">
                <img src="<?= $imagePath ?>" alt="<?= $movieTitle ?>" class="movie-thumbnail">
                <div class="history-info">
                    <h4 class="history-title"><?= $movieTitle ?></h4>                    
                    <div class="history-actions-item">
                        <form method="post">
                            <input type="hidden" name="watch_title" value="<?= $row['movie_title'] ?>">
                            <button type="submit" class="btn-watch">▶ Rewatch</button>
                        </form>
                        <form method="post" id="removeHistoryForm-<?= $row['id'] ?>">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="button" class="btn-remove" onclick="showRemoveModal(<?= $row['id'] ?>)">❌ Remove</button>
                        </form> 
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <div class="empty-history">
            <h3>No history found</h3>
            <p>You haven't watched any movies yet!</p>
        </div>
    <?php endif; ?>
</div>

<script>
    const movies = [
        { title: "Moana 2", img: "moviepics/moana2.jpg" },
        { title: "Sonic the Hedgehog 3", img: "moviepics/sonic.jpg" },
        { title: "Red One", img: "moviepics/redone.jpg" },
        { title: "Transformers One",  img: "moviepics/transformers.jpg" },
        { title: "Venom: The Last Dance", img: "moviepics/venom.jpg" },
        { title: "Deadpool & Wolverine", img: "moviepics/deadpool.jpg" },
        { title: "Despicable Me 4", img: "moviepics/despicable.jpg" },
        { title: "Elemental", img: "moviepics/elemental.jpeg" },
        { title: "The Wild Robot",  img: "moviepics/thewildrobot.jpg" },
        { title: "Inside Out 2", img: "moviepics/insideout2.jpg" },
        { title: "Leo", img: "moviepics/leo.jpg" },
        { title: "Turning Red", img: "moviepics/red.jpg" },
        { title: "Dolittle", img: "moviepics/dolittle.jpg" },
        { title: "Spellbound",  img: "moviepics/spellbound.jpg" },
        { title: "The Super Mario Bros", img: "moviepics/mario.jpg" },
        { title: "Trolls Band Together", img: "moviepics/trolls.jpg" },
        { title: "The Little Mermaid", img: "moviepics/mermaid.jpg" },
        { title: "A Quiet Place: Day One", img: "moviepics/day one.jpg" },
        { title: "Kung Fu Panda 4",  img: "moviepics/kungfupanda4.jpg" },
        { title: "Migration", img: "moviepics/migration.jpg" }
    ];

    const searchBar = document.getElementById("searchBar");
    const clearSearch = document.getElementById("clearSearch");
    const resultsContainer = document.getElementById("searchResults");

    searchBar.addEventListener("input", function() {
        try {
            let query = this.value.toLowerCase();
            
            if (query === "") {
                resultsContainer.style.display = "none";
                clearSearch.style.display = "none";
                return;
            }

            clearSearch.style.display = "block";
            
            let filteredMovies = movies.filter(movie => movie.title.toLowerCase().includes(query));

            resultsContainer.innerHTML = filteredMovies.map(movie => `
                <div class="search-item" onclick="redirectToMovie('${movie.title}')">
                    <img src="${movie.img}" alt="${movie.title}">
                    <div>
                        <h4>${movie.title}</h4>
                    </div>
                </div>
            `).join("");

            resultsContainer.style.display = "block";
        } catch (error) {
            console.error('Error in search:', error);
        }
    });

    clearSearch.addEventListener("click", function() {
        try {
            searchBar.value = "";
            resultsContainer.style.display = "none";
            clearSearch.style.display = "none";
            searchBar.focus();
        } catch (error) {
            console.error('Error clearing search:', error);
        }
    });

    document.addEventListener("click", function(event) {
        try {
            if (!searchBar.contains(event.target) && !resultsContainer.contains(event.target)) {
                resultsContainer.style.display = "none";
            }
        } catch (error) {
            console.error('Error in document click:', error);
        }
    });

    function redirectToMovie(movieTitle) {
        window.location.href = 'md.php?title=' + encodeURIComponent(movieTitle);
    }
    
    document.getElementById('logoutLink').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('logoutModal').style.display = 'flex';
    });

    document.getElementById('confirmLogout').addEventListener('click', function() {
        window.location.href = 'login.php?logout=true';
    });

    document.getElementById('cancelLogout').addEventListener('click', function() {
        document.getElementById('logoutModal').style.display = 'none';
    });

    document.getElementById('logoutModal').addEventListener('click', function(event) {
        if (event.target === this) {
            this.style.display = 'none';
        }
    });

    document.getElementById('clearHistoryBtn').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('clearHistoryModal').style.display = 'flex';
    });

    document.getElementById('confirmClearHistory').addEventListener('click', function() {
        document.getElementById('clearHistoryForm').submit();
    });

    document.getElementById('cancelClearHistory').addEventListener('click', function() {
        document.getElementById('clearHistoryModal').style.display = 'none';
    });

    document.getElementById('clearHistoryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            this.style.display = 'none';
        }
    });

    let currentRemoveFormId = null;

    function showRemoveModal(historyId) {
        currentRemoveFormId = 'removeHistoryForm-' + historyId;
        document.getElementById('removeHistoryModal').style.display = 'flex';
    }

    document.getElementById('confirmRemoveHistory').addEventListener('click', function() {
        if (currentRemoveFormId) {
            document.getElementById(currentRemoveFormId).submit();
        }
    });

    document.getElementById('cancelRemoveHistory').addEventListener('click', function() {
        document.getElementById('removeHistoryModal').style.display = 'none';
        currentRemoveFormId = null;
    });

    document.getElementById('removeHistoryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            this.style.display = 'none';
            currentRemoveFormId = null;
        }
    });
</script>
</body>
</html>