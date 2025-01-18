<?php 
    include_once 'baza.php';
    include_once 'seja.php';

    // Enable error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Log errors to a specific file
    ini_set('log_errors', 1);
    ini_set('error_log', 'error.log');
?>

<header>
    <div>LAN PARTY 2024/2025</div>
    <div>
        <ol>
            <li><a href="index.php">Doma</a></li>
            <li><a href="team.php">Ekipa</a></li>
            <li><a href="sponsors.php">Sponzorji</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">O nas</a></li>
        </ol>
    </div>
    <div>
        Prijava
        <img src="" alt="Person">
    </div>
</header>