<?php
// Get the movie title from the URL
$movieTitle = isset($_GET['title']) ? $_GET['title'] : '';

// Define an array of movies with their details
$movies = [
    'Moana 2' => [
        'id' => 1, 
        'title' => 'Moana 2', 
        'poster' => 'moviepics/moana2.jpg', 
        'background' => 'moviepics/moana22.jpg',
        'description' => 'After receiving an unexpected call from her wayfinding ancestors, Moana journeys alongside Maui and a new crew to the far seas of Oceania and into dangerous, long-lost waters for an adventure unlike anything she\'s ever faced.',
        'duration' => '1 hour and 40 minutes',
        'genre' => 'Animated, Musical, Adventure, Comedy, Family, Fantasy',
        'cast' => 'Auli\'i Cravalho (voice of Moana) Dwayne Johnson (voice of Maui) Alan Tudyk ',
        'country' => 'United States',
        'production' => 'Walt Disney Animation Studios'
    ],
    'Sonic the Hedgehog 3' => [
        'id' => 2, 
        'title' => 'Sonic the Hedgehog 3', 
        'poster' => 'moviepics/sonic.jpg', 
        'background' => 'moviepics/sonicc.webp',
        'description' => 'Sonic, Knuckles, and Tails reunite against a powerful new adversary, Shadow, a mysterious villain with powers unlike anything they have faced before. With their abilities outmatched in every way, Team Sonic must seek out an unlikely alliance in hopes of stopping Shadow and protecting the planet.',
        'duration' => '1 hour and 49 minutes',
        'genre' => 'Action, Adventure, Comedy, Family, Fantasy, and Sci-fi. ',
        'cast' => 'Ben Schwartz (voice of Sonic), Colleen O\'Shaughnessey (voice of Tails), Idris Elba, and Keanu Reeves. ',
        'country' => 'Japan, United States',
        'production' => 'Paramount Pictures, Sega Sammy Group, Original Film, Marza Animation Planet, Blur Studio'
    ],
    'Red One' => [
        'id' => 3, 
        'title' => 'Red One', 
        'poster' => 'moviepics/redone.jpg', 
        'background' => 'moviepics/redonee.webp',
        'description' => 'After Santa Claus (codename: Red One) is kidnapped, the North Pole\'s Head of Security must team up with the world\'s most infamous bounty hunter in a globe-trotting, action-packed mission to save Christmas.',
        'duration' => '2 hours and 2 minutes',
        'genre' => 'Holiday, Action, Adventure, Comedy',
        'cast' => 'Dwayne Johnson',
        'country' => 'United States',
        'production' => 'The Detective Agency, Metro-Goldwyn-Mayer, Chris Morgan Productions, Seven Bucks Productions'
    ],
    'Transformers One' => [
        'id' => 4, 
        'title' => 'Transformers One', 
        'poster' => 'moviepics/transformers.jpg', 
        'background' => 'moviepics/transformerss.jpeg',
        'description' => 'The fate of humanity is at stake when two races of robots, the good Autobots and the villainous Decepticons, bring their war to Earth. The robots have the ability to change into different mechanical objects as they seek the key to ultimate power, but only human Sam Witwicky can save the world from total destruction.',
        'duration' => '104 minutes',
        'genre' => 'Action, Adventure, Sci-Fi, Fantasy, Animation',
        'cast' => 'Chris Hemsworth, Brian Tyree Henry, Scarlett Johansson, Keegan-Michael Key, Steve Buscemi, Jon Hamm',
        'country' => 'United States',
        'production' => 'Entertainment One, Hasbro Entertainment, Paramount Animation, Nickelodeon Movies'
    ],
    'Venom: The Last Dance' => [
        'id' => 5, 
        'title' => 'Venom: The Last Dance', 
        'poster' => 'moviepics/venom.jpg', 
        'background' => 'moviepics/venomm.png',
        'description' => 'Eddie and Venom are on the run. Hunted by both of their worlds and with the net closing in, the duo are forced into a devastating decision that will bring the curtains down on Venom and Eddie\'s last dance.',
        'duration' => '1 hour and 50 minutes',
        'genre' => 'Action, Thriller',
        'cast' => 'Tom Hardy (Eddie/Venom), Juno Temple (Dr. Paine), Chiwetel Ejiofor (Strickland), Rhys Ifans (Martin), Stephen Graham (Mulligan)',
        'country' => 'United States',
        'production' => 'Arad Productions, Columbia Pictures, Marcel Hardy, Marvel Entertainment, Matt Tolmach Productions, Pascal Pictures'
    ],

    'Deadpool & Wolverine' => [
        'id' => 6, 
        'title' => 'Deadpool & Wolverine', 
        'poster' => 'moviepics/deadpool.jpg', 
        'background' => 'moviepics/deadpooll.jpg',
        'description' => 'A listless Wade Wilson toils away in civilian life with his days as the morally flexible mercenary, Deadpool, behind him. But when his homeworld faces an existential threat, Wade must reluctantly suit-up again with an even more reluctant Wolverine.',
        'duration' => '2 hours and 7 minutes',
        'genre' => 'Action, Adventure, Comedy, and Sci-Fi.',
        'cast' => 'Ryan Reynolds, Hugh Jackman',
        'country' => 'United States',
        'production' => 'Marvel Studios'
    ],
    'Despicable Me 4' => [
        'id' => 7, 
        'title' => 'Despicable Me 4', 
        'poster' => 'moviepics/despicable.jpg', 
        'background' => 'moviepics/despicablee.jpg',
        'description' => 'Gru and Lucy and their girls — Margo, Edith and Agnes — welcome a new member to the Gru family, Gru Jr., who is intent on tormenting his dad. Gru faces a new nemesis in Maxime Le Mal and his femme fatale girlfriend Valentina, and the family is forced to go on the run.',
        'duration' => '94 minutes',
        'genre' => 'Comedy, Adventure, Animation, and Kids & Family',
        'cast' => 'Steve Carell, Kristen Wiig, Pierre Coffin, Joey King, Miranda Cosgrove, Stephen Colbert, and Sofía Vergara',
        'country' => 'United States',
        'production' => 'Illumination Entertainment'
    ],
    'Elemental' => [
        'id' => 8, 
        'title' => 'Elemental', 
        'poster' => 'moviepics/elemental.jpeg', 
        'background' => 'moviepics/elementall.jpg',
        'description' => 'In a city where fire, water, land and air residents live together, a fiery young woman and a go-with-the-flow guy will discover something elemental: how much they have in common.',
        'duration' => '101 minutes',
        'genre' => 'Adventure, Family, Romance, and Interspecies Romance. ',
        'cast' => 'Leah Lewis: as Ember Lumen, Mamoudou Athie: as Wade Ripple, Ronnie del Carmen: as Ember\'s father, Shila Ommi: as Ember\'s mother ',
        'country' => 'United States',
        'production' => 'Pixar Animation Studios'
    ],
    'The Wild Robot' => [
        'id' => 9, 
        'title' => 'The Wild Robot', 
        'poster' => 'moviepics/thewildrobot.jpg', 
        'background' => 'moviepics/thewildrobott.jpg',
        'description' => 'After a shipwreck, an intelligent robot called Roz is stranded on an uninhabited island. To survive the harsh environment, Roz bonds with the island\'s animals and cares for an orphaned baby goose.',
        'duration' => '102 minutes',
        'genre' => 'Animated, Science Fiction, Adventure, Comedy ',
        'cast' => 'Lupita Nyong\'o, Pedro Pascal, Catherine O\'Hara, Kit Connor, Bill Nighy, Stephanie Hsu, Matt Berry, Ving Rhames, Mark Hamill ',
        'country' => 'America',
        'production' => 'DreamWorks Animation'
    ],
    'Inside Out 2' => [
        'id' => 10, 
        'title' => 'Inside Out 2', 
        'poster' => 'moviepics/insideout2.jpg', 
        'background' => 'moviepics/insideout22.jpg',
        'description' => 'Teenager Riley\'s mind headquarters is undergoing a sudden demolition to make room for something entirely unexpected: new Emotions! Joy, Sadness, Anger, Fear and Disgust, who\'ve long been running a successful operation by all accounts, aren\'t sure how to feel when Anxiety shows up. And it looks like she\'s not alone.',
        'duration' => '96 minutes',
        'genre' => 'Coming-of-age and Animation',
        'cast' => 'Amy Poehler as Joy, Maya Hawke as Anxiety, Ayo Edebiri as Envy, Paul Walter Hauser as Embarrassment, Adèle Exarchopoulos as Ennui, June Squibb as Nostalgia, Liza Lapira as Riley, Kensington Tallman as Riley, Tony Hale as Bing Bong, Lewis Black as Anger, Phyllis Smith as Sadness, Kyle MacLachlan as Dad, Diane Lane as Mom',
        'country' => 'United States',
        'production' => 'Pixar Animation Studios'
    ],

    'Leo' => [
        'id' => 11, 
        'title' => 'Leo', 
        'poster' => 'moviepics/leo.jpg', 
        'background' => 'moviepics/leoo.jpg',
        'description' => 'Jaded 74-year-old lizard Leo has been stuck in the same Florida classroom for decades with his terrarium-mate turtle. When he learns he only has one year left to live, he plans to escape to experience life on the outside but instead gets caught up in the problems of his anxious students — including an impossibly mean substitute teacher.',
        'duration' => '1 hour and 42 minutes',
        'genre' => 'Animated, Musical Comedy, Coming-of-Age ',
        'cast' => 'Adam Sandler (voice of Leo), Bill Burr (voice of Squirtle), Cecily Strong (voice of Mrs. Malkin), Jason Alexander (voice of Jayda\'s Dad), RobSchneider (voice of Principal), Sadie Sandler (voice of Jayda), Sunny Sandler (voice of Summer), Jackie Sandler (voice of Jayda\'s Mom), Heidi Gardner (voice of Eli\'s Mom), Nick Swardson (voice of Bunny), Nicholas Turturro (voice of Anthony\' Dad), Robert Smigel (voice of Miniature Horse), Jo Koy (voice of Coach Komura), Stephanie Hsu (voice of Skyler\'s Mom), Kevin James (voice of Ace) ',
        'country' => 'USA',
        'production' => 'Happy Madison Productions '
    ],
    'Turning Red' => [
        'id' => 12, 
        'title' => 'Turning Red', 
        'poster' => 'moviepics/red.jpg', 
        'background' => 'moviepics/redd.webp',
        'description' => 'Thirteen-year-old Mei is experiencing the awkwardness of being a teenager with a twist-when she gets too excited, she transforms into a giant red panda.',
        'duration' => '1 hour and 40 minutes',
        'genre' => 'Coming-of-age comedy-drama',
        'cast' => 'Rosalie Chiang and Sandra Oh',
        'country' => 'Canada',
        'production' => 'Pixar Animation Studios'
    ],
    'Dolittle' => [
        'id' => 13, 
        'title' => 'Dolittle', 
        'poster' => 'moviepics/dolittle.jpg', 
        'background' => 'moviepics/dolittlee.jpg',
        'description' => 'A reclusive doctor who can talk to animals, Dr. John Dolittle, embarks on a quest to find a cure for the ill Queen Victoria, venturing to a mythical island with his animal companions. ',
        'duration' => '101 minutes',
        'genre' => 'United States',
        'cast' => 'Robert Downey Jr., Antonio Banderas, Michael Sheen, Emma Thompson (voice), Rami Malek (voice), John Cena (voice), Tom Holland (voice), Selena Gomez (voice)',
        'country' => 'United States',
        'production' => 'Universal Pictures, Team Downey, Perfect World Pictures'
    ],
    'Spellbound' => [
        'id' => 14, 
        'title' => 'Spellbound', 
        'poster' => 'moviepics/spellbound.jpg', 
        'background' => 'moviepics/spellboundd.jpg',
        'description' => 'After receiving an unexpected call from her wayfinding ancestors, Moana journeys alongside Maui and a new crew to the far seas of Oceania and into dangerous, long-lost waters for an adventure unlike anything she\'s ever faced.',
        'duration' => '111 minutes',
        'genre' => 'Psychological thriller, mystery',
        'cast' => 'Ingrid Bergman, Gregory Peck, Michael Chekhov, Leo G. Carroll',
        'country' => 'United States',
        'production' => 'Skydance Animation'
    ],
    'The Super Mario Bros' => [
        'id' => 15, 
        'title' => 'The Super Mario Bros', 
        'poster' => 'moviepics/mario.jpg', 
        'background' => 'moviepics/marioo.jpg',
        'description' => 'While working underground to fix a water main, Brooklyn plumbers—and brothers—Mario and Luigi are transported down a mysterious pipe and wander into a magical new world. But when the brothers are separated, Mario embarks on an epic quest to find Luigi.',
        'duration' => '92 minutes',
        'genre' => 'Animation, Adventure, Comedy, Fantasy',
        'cast' => 'Chris Pratt, Anya Taylor-Joy, Charlie Day, Jack Black, Seth Rogen',
        'country' => 'United States, Japan',
        'production' => 'Nintendo, Illumination, Universal Pictures'
    ],

    'Trolls Band Together' => [
        'id' => 16, 
        'title' => 'Trolls Band Together', 
        'poster' => 'moviepics/trolls.jpg', 
        'background' => 'moviepics/trollss.jpg',
        'description' => 'When Branch\'s brother, Floyd, is kidnapped for his musical talents by a pair of nefarious pop-star villains, Branch and Poppy embark on a harrowing and emotional journey to reunite the other brothers and rescue Floyd from a fate even worse than pop-culture obscurity.',
        'duration' => '92 minutes',
        'genre' => 'Animation, Adventure, Comedy, Family, Fantasy, Musical',
        'cast' => 'Anna Kendrick, Justin Timberlake, Camila Cabello, Eric André, Kid Cudi, Troye Sivan, Zooey Deschanel',
        'country' => 'United States',
        'production' => 'DreamWorks Animation, Universal Pictures'
    ],
    'The Little Mermaid' => [
        'id' => 17, 
        'title' => 'The Little Mermaid', 
        'poster' => 'moviepics/mermaid.jpg', 
        'background' => 'moviepics/mermaidd.jpg',
        'description' => 'The youngest of King Triton\'s daughters, and the most defiant, Ariel longs to find out more about the world beyond the sea, and while visiting the surface, falls for the dashing Prince Eric. With mermaids forbidden to interact with humans, Ariel makes a deal with the evil sea witch, Ursula, which gives her a chance to experience life on land, but ultimately places her life - and her father\'s crown - in jeopardy.',
        'duration' => '135 minutes',
        'genre' => 'Adventure, Family, Fantasy, Musical, Romance',
        'cast' => 'Halle Bailey, Jonah Hauer-King, Melissa McCarthy, Javier Bardem, Daveed Diggs, Awkwafina',
        'country' => 'United States',
        'production' => 'Walt Disney Pictures, Lucamar Productions, Marc Platt Productions'
    ],
    'A Quiet Place: Day One' => [
        'id' => 18, 
        'title' => 'A Quiet Place: Day One', 
        'poster' => 'moviepics/day one.jpg', 
        'background' => 'moviepics/day onee.jpg',
        'description' => 'As New York City is invaded by alien creatures who hunt by sound, a woman named Sammy fights to survive.',
        'duration' => '99 minutes',
        'genre' => 'Horror, Science Fiction, Mystery & Thriller',
        'cast' => 'Halle Bailey as Ariel, Jonah Hauer-King as Prince Eric, Melissa McCarthy as Ursula, Javier Bardem as King Triton, Daveed Diggs as Sebastian (voice), Awkwafina as Scuttle (voice)',
        'country' => 'United States',
        'production' => 'Platinum Dunes, Sunday Night Productions'
    ],
    'Kung Fu Panda 4' => [
        'id' => 19, 
        'title' => 'Kung Fu Panda 4', 
        'poster' => 'moviepics/kungfupanda4.jpg', 
        'background' => 'moviepics/kungfupanda44.jpg',
        'description' => 'Po is gearing up to become the spiritual leader of his Valley of Peace, but also needs someone to take his place as Dragon Warrior. As such, he will train a new kung fu practitioner for the spot and will encounter a villain called the Chameleon who conjures villains from the past.',
        'duration' => '93 minutes',
        'genre' => 'Animation, Martial Arts, Comedy, Adventure',
        'cast' => 'Jack Black, Awkwafina, Bryan Cranston, James Hong, Ian McShane, Ke Huy Quan, Ronny Chieng, Lori Tan Chinn, Dustin Hoffman, Viola Davis',
        'country' => 'United States',
        'production' => 'DreamWorks Animation'
    ],
    'Migration' => [
        'id' => 20, 
        'title' => 'Migration', 
        'poster' => 'moviepics/migration.jpg', 
        'background' => 'moviepics/migrationn.jpg',
        'description' => 'After a migrating duck family alights on their pond with thrilling tales of far-flung places, the Mallard family embarks on a family road trip, via New York City, to tropical Jamaica.',
        'duration' => '83 minutes',
        'genre' => 'Animation, Adventure, Comedy',
        'cast' => 'Kumail Nanjiani, Elizabeth Banks, Keegan-Michael Key, Awkwafina, Danny DeVito',
        'country' => 'United States',
        'production' => 'Illumination'
    ],
    
];

// Find the movie details based on the title
$movie = isset($movies[$movieTitle]) ? $movies[$movieTitle] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $movie ? $movie['title'] : 'Movie Details' ?> - FreeFlix</title>
    <link rel="stylesheet" href="css/md.css">
</head>
<header>
    <div class="badge-container">
        <a href="homepage.php">
        <span class="free-badge">Free</span>
        <span class="flix-text">Flix</span>
    </a>
    </div>
    <nav>           
        <a href="homepage.php">Home</a>
        <a href="history.php">Watch History</a>
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

<!-- Added logout modal HTML -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal">
        <!-- Using a simple SVG for the logout icon; you can replace with an image if preferred -->
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

<body>
    <?php if ($movie): ?>
    <div class="movie-container">
        <div class="movie-background" style="background-image: url('<?= $movie['background'] ?>');"></div>
        <div class="movie-content">
            <img src="<?= $movie['poster'] ?>" alt="<?= $movie['title'] ?>" class="movie-poster">
            <div class="movie-details">
                <h1 class="movie-title"><?= $movie['title'] ?></h1>
                <form action="movie.php" method="GET">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($movieTitle); ?>">
                    <button type="submit" class="watch-button">▶ Watch now</button>
                </form>
                <p class="movie-overview"><?= $movie['description'] ?></p>
                <div class="movie-meta">
                    <div class="meta-item">
                        <strong>Genre</strong>
                        <?= $movie['genre'] ?>
                    </div>
                    <div class="meta-item">
                        <strong>Duration</strong>
                        <?= $movie['duration'] ?>
                    </div>
                    <div class="meta-item">
                        <strong>Country</strong>
                        <?= $movie['country'] ?>
                    </div>
                    <div class="meta-item">
                        <strong>Production</strong>
                        <?= $movie['production'] ?>
                    </div>
                    <div class="meta-item">
                        <strong>Cast</strong>
                        <?= $movie['cast'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">
            No movie found with the title "<?= htmlspecialchars($movieTitle) ?>".
        </div>
    <?php endif; ?>

    <section class="movies">
        <h2>Suggestions</h2>
        <div class="grid1">
            <div class="movie-card">
                <img src="moviepics/moana2.jpg" alt="Moana 2">
                <h4>Moana 2</h4>
                <button class="watch-now" onclick="redirectToMovie('Moana 2')">▶ Watch now</button>
            </div>
            <div class="movie-card">
                <img src="moviepics/sonic.jpg" alt="Sonic the Hedgehog 3">
                <h4>Sonic the Hedgehog 3</h4>
                <button class="watch-now" onclick="redirectToMovie('Sonic the Hedgehog 3')">▶ Watch now</button>
            </div>
            <div class="movie-card">
                <img src="moviepics/redone.jpg" alt="Red One">
                <h4>Red One</h4>
                <button class="watch-now" onclick="redirectToMovie('Red One')">▶ Watch now</button>
            </div>
            <div class="movie-card">
                <img src="moviepics/transformers.jpg" alt="Transformers One">
                <h4>Transformers One</h4>
                <button class="watch-now" onclick="redirectToMovie('Transformers One')">▶ Watch now</button>
            </div>
            <div class="movie-card">
                <img src="moviepics/venom.jpg" alt="Venom: The Last Dance">
                <h4>Venom: The Last Dance</h4>
                <button class="watch-now" onclick="redirectToMovie('Venom: The Last Dance')">▶ Watch now</button>
            </div>
            <div class="movie-card">
                <img src="moviepics/deadpool.jpg" alt="Deadpool & Wolverine">
                <h4>Deadpool & Wolverine</h4>
                <button class="watch-now" onclick="redirectToMovie('Deadpool & Wolverine')">▶ Watch now</button>
            </div>
        </div>  
    </section>

    <script>
        const movies = [
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

        // Close modal if clicking outside of it
        document.getElementById('logoutModal').addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });

        const SuggestionsMovies = [
            { title: "Moana 2", image: "moviepics/moana2.jpg" },
            { title: "Sonic the Hedgehog 3", image: "moviepics/sonic.jpg" },
            { title: "Red One", image: "moviepics/redone.jpg" },
            { title: "Transformers One", image: "moviepics/transformers.jpg" },
            { title: "Venom: The Last Dance", image: "moviepics/venom.jpg" },
            { title: "Deadpool & Wolverine", image: "moviepics/deadpool.jpg" },
            { title: "Despicable Me 4", image: "moviepics/despicable.jpg" },
            { title: "Elemental", image: "moviepics/elemental.jpeg" },
            { title: "The Wild Robot", image: "moviepics/thewildrobot.jpg" },
            { title: "Inside Out 2", image: "moviepics/insideout2.jpg" },
            { title: "Leo", image: "moviepics/leo.jpg" },
            { title: "Turning Red", image: "moviepics/red.jpg" },
            { title: "Dolittle", image: "moviepics/dolittle.jpg" },
            { title: "Spellbound", image: "moviepics/spellbound.jpg" },
            { title: "The Super Mario Bros", image: "moviepics/mario.jpg" },
            { title: "Trolls Band Together", image: "moviepics/trolls.jpg" },
            { title: "The Little Mermaid", image: "moviepics/mermaid.jpg" },
            { title: "A Quiet Place: Day One", image: "moviepics/day one.jpg" },
            { title: "Kung Fu Panda 4", image: "moviepics/kungfupanda4.jpg" },
            { title: "Migration", image: "moviepics/migration.jpg" }
        ];

        function getRandomUniqueItems(array, count) {
            const shuffled = [...array].sort(() => 0.5 - Math.random());
            return shuffled.slice(0, count);
        }

        function updateSuggestionsMovies() {
            const selectedMovies = getRandomUniqueItems(SuggestionsMovies, 6); 
            const movieCards = document.querySelectorAll('.movie-card');

            movieCards.forEach((card, index) => {
                if (index < selectedMovies.length) {
                    const movie = selectedMovies[index];
                    card.querySelector('img').src = movie.image;
                    card.querySelector('img').alt = movie.title;
                    card.querySelector('h4').textContent = movie.title;
                    card.querySelector('.watch-now').setAttribute('onclick', `redirectToMovie('${movie.title}')`);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', updateSuggestionsMovies);
    </script>
</body>
</html>