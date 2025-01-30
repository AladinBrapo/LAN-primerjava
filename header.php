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
    <div class="header-logo"><a href="index.php">LAN PARTY 2024/2025</a></div>
    <nav class="header-nav">
        <ul>
            <li><a href="team.php">Ekipe</a></li>
            <li>|</li>
            <li><a href="sponsors.php">Sponzorji</a></li>
            <li>|</li>
            <li><a href="blog.php">Blog</a></li>
            <li>|</li>
            <li><a href="about.php">O nas</a></li>
        </ul>
    </nav>
    <?php
        if (isset($_SESSION['log'])) {
            $i = $_SESSION['im'];
            $p = $_SESSION['pr'];
            echo '
            <div class="header-login">
                <span class="user-info">' . htmlspecialchars($i) . ' ' . htmlspecialchars($p) . '</span>
                <a href="logout.php">
                    <span>Odjava</span>
                    <img src="img/login.png" alt="Person">
                </a>
            </div>'; 
        } else {
            echo '
            <div class="header-login">
                <a href="login.php">
                    <span>Prijava</span>
                    <img src="img/login.png" alt="Person">
                </a>
            </div>';
        }
    ?>
</header>
