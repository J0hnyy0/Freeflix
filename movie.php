<?php
session_start();
require 'config.php'; // Include database connection

// Require user to be logged in
requireLogin();

$movie_title = $_GET['title'] ?? null;
$user_id = $_SESSION['user_id'] ?? 0;

$movies = [
    [
        "title" => "Moana 2", 
        "file" => "https://macdn.hakunaymatata.com/resource/24111aa1dab41ea96f20191997cebd07.mp4?Expires=1747635202&Signature=4LkL~1Fx3NBPkuIHkDAW7CxrBYhW27AY3Cz-BbP0jFI5wCbsE2zwy2J9L0J2PRH-ox9fJz3XY1uW50NRiKpHkIHfmrE5UTKIlxZdI6fMJzIbWM6HrNyOjnx8qp9Pg1RbCreXZS4Vy09rvaKmec0cqt4UH0Rzz3ZCdKRI-C1v32UFpHRRcYP6WB81khWVjHVvlCM4CANGgDhmwXklhRZrPM5cV8fJmDjPzsB-AVeZgjXCGbnCZ1rtDJhIsOXnYU-lVPT7NAadO96NyhUe3GgEJ~vTxOxg3LEMTu7op6ikqswvSP8KBT~l3wLCo23D00HmLBbn1fIgKbRF2q~df3Qh5A__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Sonic the Hedgehog 3", 
        "file" => "https://macdn.hakunaymatata.com/resource/7872105ab845b8c249281bde58610f65.mp4?Expires=1747634426&Signature=fn3qkd0FdeIOamzMJpSzViB5kWToqdG~UzVukGYJ9Y9npTQIAEibZJvUIGG-Vedn-W68iYQO1wGLckk69gm~Fwh1G6jjQkP-clPtn51se1~PcpMrP8GAT2krI0FCbEGnwR-7nDPXjHrUgITr6Z-0Xo72m6mc7wu2VGXSaaaNxPeXVsAEg8wGakaAQxrbJ9QdjbCMEjJZ6cpzCuSa7IL3Ce7rhMhyMzS3X8-dAFQ4HhY8N4kCHLmLnGuyCWJlBRpLMJ6i86NivQdD2YstGZ4mvnwg8oi88ABOBwGDhYHjrjd-2qHQ1JeXfclIOBDqcqwG36dk2JcpNwqIBSBW~4TCew__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Red One", 
        "file" => "https://macdn.hakunaymatata.com/resource/77b758d174aae87ff5f4052f02285112.mp4?Expires=1747635746&Signature=LXm0DqQKawrQV3BlrQP1q9lTmTil~ckShHGzg2OZxLFzmjFT3es0vGsfX33JLtjlGrFumXennG2xWnyYeChXGv5TTyck5J7snRc9lVuPFqXdTTpP9kQr--0GTTqpYuQAu-BxMsksfv9N9toYnAJUxHX6Pq~t50F7tSTlMB836y0IG-X1uShq3qxG8X47Qflw1Le-8sTgP-4XzNsO0O47rh5a1a8J7YHbmbxirOOYsJQUg-OAxGTjbj~9JRCJk2kIX0nSHxXoUxNDLtqOaD3yOfSDE17q8FLzqtv1yeg-AfU9uEH3Wa62vibwaw-zLun8Y9FZ0rx3g4JBtw1~KC-IPQ__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Transformers One", 
        "file" => "https://macdn.hakunaymatata.com/resource/3bdec071e01182018284d97ad4b9ca58.mp4?Expires=1747632579&Signature=SYW83oBRxyAboxhHEvDEZFzkU-jigdnpTO8pT6uaMvyA7bUmAQShlvMjiuA8tqSQJYC9UgYKjV7O38Hb9ktaSx88ksTPjIiQFW3w7M5QSyRDDpUyQ0N92nka~gJwOM7DBgofUh69-YrRlQGKxq~8S9n~VfN5qQB5k8SGJtmecIvFNA6-4jei4K9W1FiBpAxYUk7dDT33WogIbu8ano2SCWc15ogeSFX8CPyKRgfYkav66fGtG27O2xJuL9Uw20PWly2rp89eVQK~qvDw6YcpjJA7EJlg-9~dTnALc1bEYaNk-lfLiftibvWzJWBt-OV0ULzCDTPftOeHS479B-fBng__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Venom: The Last Dance", 
        "file" => "https://vgorigin.hakunaymatata.com/resource/773ed8f9047f322d6a725b58f309dcc8.mp4?Expires=1747635002&KeyName=wefeed&Signature=6LOCHe6msaXccbCE8qkIoZ-F6P-wqeSfeDiWvM7KSImLe0pKpPyzOapt8txT-cimCMntgn4zBMsY9mEpb7GdBQ"
    ],
    [
        "title" => "Deadpool & Wolverine", 
        "file" => "https://macdn.hakunaymatata.com/resource/bfc864c7cab4733875ac3651c6542c9e.mp4?Expires=1747634411&Signature=OV390AKJhxQkIlV4mQ1wH1FupPOh6d3CjfNHpQgLNxp103yit5AI32BQW2cbOaHnPfLrl32MA2xv6KRjlqHX4R~osPctsNx8w9VU-8YOQSgc8ZBbMec0MwIuU6QWOdj4j53U2aGDnt~FrG~R9YTDTvG7C7~~2SL4jkwCFE0Zy-aYTdMI5~AaVMttZwqW82LNeY0fXfPRAXFbdhsgH0ce~6YvQm2OsY5T9whMKICWcAVaq759l-9by-wbKFOsvXPg8bTwvjpMMOXMapZbZwzP0Xnt3ClTnufwNhSbhZ3jJ5MuFPCGWzzTt-bKpToIt5i8hcAuJojITxlGNdbvQ--KJg__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Despicable Me 4", 
        "file" => "https://macdn.hakunaymatata.com/resource/dc914dc29683ed3b7248c9a845c0b6e9.mp4?Expires=1747636431&Signature=dcAenKrk7YzAXvHj0Y~wyIAjkzmBObwLfcAekRuCmTug466AO2M716W6gni--oRtph5XgkC~lB1TnLhRlzCriGBIWJiWtFGBmON5S8QU862B9bE5bAS8ruWY~3rpMHVQh65EMkvU6lB265jDqNrVo3U4ml09CR1WbvZ6SBfVjtILoSTxLT9XWbsjNDD6jL0BAs-bJMXqHU75QWzlsTdnv7fE8~tL5Cvx-kJhnOkBa2m8C011nxI2R0OBaDUb5O71e0vTus2lZdMTD08UQB2Fqvztto-mMU2-FSc71ono-zXkju8VqkvaIpR5g~6n2epoe7dZ14I-PoFrz4hrSuzrGA__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Elemental", 
        "file" => "https://macdn.hakunaymatata.com/resource/4db9e031b1e8130746d5622509764604.mp4?Expires=1747636447&Signature=v9~j8m1hNS730gimV58hrPZeeFPugLl~9zwUo2I6xKz3x3HPEIe-VFrDepvCsyeiafvMWKALd0qdWhO21TdxL6iENDhR2~bpsoM1O3Wtwo3~8Wq7TtXx3HrNujwsOxgM8C77ldOibw7Lsas4ke2p7Qzmozu~QJIkvjLcs7h4RYJCocoqdqAH4-PhH4UP4ZWR84fDQnFHSlWPwaAgKPG7PSdtf45dXgwrHi9e7msRmtxEy2KZytclGrGBGSf9xI0MMwHAkErCCUrJUYaGiR89t9ku5bJJooo9RsN5CN7ucZVgOKW3JmEk6Z~lVAthhG-N1Qef0vmA-1F3sFAWMVmUvQ__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "The Wild Robot", 
        "file" => "https://vgorigin.hakunaymatata.com/resource/e32814a3736559b9341461494546e911.mp4?Expires=1747632895&KeyName=wefeed&Signature=eQJ4R6quGwIh8eYkEQ1cYrAo1pF4o4IOfE84OidVCElAfj36EFPwz7HtJ311IzggZlsvg6nZXp5DcbMlih2CCA"
    ],
    [
        "title" => "Inside Out 2", 
        "file" => "https://macdn.hakunaymatata.com/resource/a8d61980a7fab9181423f7c3cb63d92a.mp4?Expires=1747634018&Signature=bAfUzBHRTampVm2-W8QX6wJbMA0OjQ3EEuZx-Pcd9FcbKwqj9je~txlq5GxF4IR~SuZqgGRRYBlxopbjdvuOtt~WH30Cwjc-dQOBVrQbLXz-7-Cm2H8dlyL1pXst1ApVi65O02rkXlIe1xOiO1sYvp-M-MMzVa3uRXo2qtlgnVlJCUYK0wY8dhfF~qFZulyHMX-unYQ~6edHTAl-awqcRVyrJddjB0lSZ53T1KPsBHppkMxLQwz5ZKLfsuQWznALg51IT9nxWFoJJh0E~0yZ72MdPFiPR2o1J2i~xTWtPwmNis1w0~7RBrA5kqRLeWM0iq2YKS1uYpcPi8oKVqDEow__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Leo", 
        "file" => "https://macdn.hakunaymatata.com/resource/80cf14789fa1a4e50807c22023d8910f.mp4?Expires=1747636603&Signature=hpHHCIONxma6TOf4WIRz71X8UMQlVEESfR0sQLEVSMtYFQtVdrMKraBRxlBErvDNjB4ui7cCuqALpovjiFDlrMDqEBDzEDUOnNLa1n0p0WKsuiNjPVGBBow6atkgU9tmWwdmmuivztYciL~nQkPNywU7oVjAr6tYpMak-UEMJRBvfk2nMDwz3fAeRboLlU4t-vQSwTBm901BNPTc42W3kOllVarQDxu2DW9qvvODYR30y3UUbOzGCHGytT2bVUliXhT1ZYl9Bkrkz~~oEeL714CGERe-ba0e1he8CGj7Fv8yAJIw9bp9cx6Ckk0Q6sDxAedFSjVeMo6d~VaMLSKHbQ__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Turning Red", 
        "file" => "https://macdn.hakunaymatata.com/resource/8237e18a6e9bf0e087a9436c4b085a8f.mp4?Expires=1747635590&Signature=e6WqCbSFICJILqI5udkCK3MpfqxyHPhkn8VJrJvC6wzO7demkmCELQYI-4w9m5kQw7WU6fjNwthyVBcZqnEoWY6ckdsQyRSymgmWMgW4axwPFax7NDhkcCz3uwpEDxgDKIMLTtm5vHIoHS8TpzD4JDPYx~g-eJVuh8CM-28v90ST0ZQ9LPZx9qOPAWgMEeSuVKebDbcLEXRFuh4izCVlnDnO787cdONVxz00hL6deNd8VC8AcqFdnFXcmNSwqAk9gp-R4O7giSZHCiqwmq6vJcoFM0m9oj6etz1~DB9g5SgPaUB0hNAwwb2coz7REMX4gaityPXNUc9-1ZN4DyjXTA__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Dolittle", 
        "file" => "https://macdn.hakunaymatata.com/resource/c763ff774c2d4b17a79d0047598fbfe1.mp4?Expires=1747636794&Signature=4yruRcgC2AA2V~RsS7TKFqiMLqQHPJyV9HXb2h0Qq8R8M8sglP5ag4vG91ctCQF562ii02RjnKsM-VtbxmgZ2T6PZ2fv8MceQEJqouMUKsy53Kco7ptUQqJoa4tu47jc7C~CRZuk0w-LFHQt1iPCBNQGYgqDYpKnQ~DkpwrJm0xENc~al3bT2StAAjyNngY8uJIp-5LnERx0VP-1XeJ24n9ubjSAvEXKLAdHccc1wZx4ElQBsGThEBhztZOVaESNmwrqxDGIdC4GDG1clDt~gd3wRYKlMNNzItGJvDZD~wmfqV0JATdCujJ-oUKzJbMYaNGM1EO2SXeY~bzmXQKawQ__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Spellbound", 
        "file" => "https://vgorigin.hakunaymatata.com/resource/1a62d80b2c3b21e402331b5849d66b33.mp4?Expires=1747634480&KeyName=wefeed&Signature=ks3xepnd1LFXKF-1roxZRHpCQYQPIJdxLcym98CE2TQlztNIgr36fk7aBTPwDGBPDO2Zp7ZGX75YK8mh6yyoBw"
    ],
    [
        "title" => "The Super Mario Bros", 
        "file" => "https://macdn.hakunaymatata.com/resource/f72f1bb019264381a9f92f4773985075.mp4?Expires=1747633745&Signature=3s755a9hH0vdYGeqcPXsx4uPc1IExnjIInPX32MFkFxvyJXDJrw69Eim9xz2jIT8UbDXS2mrP1vczZ8QKG642oCU1bAbsoKQ~FPAThamX2fXcbZjB3BnF65MoT~vbgxMaLorTHP9aiWb4t9ARIxhpdRCtuD40q3FPuDxfn98Ch0avMt0MaKi1q~lz3L4h~ZF9R-k0GCNF5US3f3PHCuiMRaeuYNLlkLl6WZbJCNq7hjh7s2s-QvkTqhvitrEbIyPuURc2St6aKSnzC7VkCjD8np50RpPUiTWmlrZDWAJF-5EgVxRRVppYJwNUHRc3tf6v~M-gKmJXcPX9y-eJJ1-VA__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Trolls Band Together", 
        "file" => "https://macdn.hakunaymatata.com/resource/64482e7c9173355b9c211be5a066c569.mp4?Expires=1747634252&Signature=PKPR5ZvvwbI7P~J3XVoZbFbZATxquq9ADTJL4d0jLNwVqi1sAY5c9py3Go66IKvecfBqYdh--SP3gEJ49cGtmJXr6zjuGcXKkb6zZ81Z11GB~Kuh1crEf6af6ceAlP0~I5bkXiMNvrQ3Kg7xBwWXDbxElTuC9B~6AWHNsKuUPcl1vWsD-M0U9Ot4bIDHob0XQIo~unn9ST6HvsGg34LE1w08l-lQUcWhFlKBT3lnAjffBQIsNRqdhMhDNW7EPxusSBjbs29HUYgr9RlTN5raDRG2~yDT8PJpjn7VxNWDowYJlyx~01e1Sy~ZVlioe1iELdTrM8bSFWRF0hktTSchrA__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "The Little Mermaid", 
        "file" => "https://macdn.hakunaymatata.com/resource/aef00e8d44516f74e300a645af7a5454.mp4?Expires=1747634470&Signature=J5lW0uw29SXaANa9s7Mn0F4y6fq5tWfml5X05MQ7PSCbKKOpepqbAn5PUxtoU9shJij6TKdLWonA0dkQ3j48jqYhEXRdPUtEPFMzEM7tL-vYWFNgc2VByAzkRfxmb-dNvqgvJP0ME73N3lixQzCY8IySGJs08CMNMB0sp4e7JR9Rmb0zkrDBAsDPIJfF7NE1pGqup-YFzw5UK4bASwATEOaCTOJ1ojRO9cLhy46NmRNwSj-bjMAYYYqPYm22UJLio3osg9nyVWfNYAPRJcV2oOiiRt5sVeCSM-PreKqdcZcT70gGmg~iuf1gLroUDwLKoc~FTl-RDkTVz84YR6UGlQ__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "A Quiet Place: Day One", 
        "file" => "https://macdn.hakunaymatata.com/resource/8a4fa479b4432cf655202402cd503a40.mp4?Expires=1747635339&Signature=KDBEEc5WttyxHJCE8MolYxlustZfR4giF29mQB89wDslB~F2hwZMnXNlv01iZrHDWBAHggPKr3AGz0V7anoikbUWqfbIu8FpWfYQbt6Y0I0QUq-su5Gd62wyhfdzwjmPEEwT95Kkr6T-T~0Wke8OnIjGmy9Hy~uEqdygqpt~QsleVbweF1ZGE0qMMyIP~TJyncmXXphHYNJ3tnyjj-nmx60lyC~9ACn29eUXxAl8RSQ7wBeZidscOHGT8mhwNAsk5XLXN0auJNeM~ovWQE2GzC399fxnJDZSKeRMeSR9SUh5WGxoGWy5rQG0AuixUHGsoONo-gkjNata2KrfKkUPxA__&Key-Pair-Id=KMHN1LQ1HEUPL"
    ],
    [
        "title" => "Kung Fu Panda 4", 
        "file" => "https://vgorigin.hakunaymatata.com/resource/2fea34fe1bc433f9ca6d6c10160f7e95.mp4?Expires=1747633826&KeyName=wefeed&Signature=kfVpXUP0bjDmot0U5VrmVvqhnEvSV3NRvBuryTseMjyNHVlOnbLg8w_NfrzQsnJO2IKmN607iDamPv8RJgCaBA"
    ],
    [
        "title" => "Migration", 
        "file" => "https://vgorigin.hakunaymatata.com/resource/ec957e0e66e6b470f19978ae0d2af8b4.mp4?Expires=1747635751&KeyName=wefeed&Signature=R6PUjkg_jdal_-rIQBEclMxWoEM1ItCBtaFFx5mrSoNzCTw4Uo-AEmLKEGoEQktkAQikRl2BDNheqBvyhQb8Dw"
    ]
];

$selected_movie = null;
foreach ($movies as $movie) {
    if ($movie['title'] === $movie_title) {
        $selected_movie = $movie;
        break;
    }
}

// Log to watch history if a valid movie is selected
if ($selected_movie && $user_id) {
    $stmt = $conn->prepare("INSERT INTO watch_history (user_id, movie_title, watch_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $movie_title);
    if (!$stmt->execute()) {
        error_log("Failed to log watch history for user_id: $user_id, movie: $movie_title");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie_title); ?> - Movie Player</title>
    <link rel="stylesheet" href="css/movie.css">
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

<div class="video-container">
    <?php if ($selected_movie): ?>
        <video width="720" controls autoplay>
            <source src="<?php echo htmlspecialchars($selected_movie["file"]); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    <?php else: ?>
        <h1>Movie not found</h1>
    <?php endif; ?>
</div>

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

    document.getElementById('logoutModal').addEventListener('click', function(event) {
        if (event.target === this) {
            this.style.display = 'none';
        }
    });
</script>        
</body>
</html>