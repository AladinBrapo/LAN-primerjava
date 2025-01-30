<?php 
require_once 'baza.php';
include_once 'seja.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = "";

if (isset($_POST['sub'])) {
    // Filtriranje vhodnih podatkov
    $m = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL);
    $p = htmlspecialchars($_POST['geslo']);

    // Preverjanje veljavnosti emaila
    if (filter_var($m, FILTER_VALIDATE_EMAIL) === false) {
        $message = '<div class="error-msg">Invalid email address.</div>';
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000); // Redirect after 2 seconds
              </script>";
    } else {
        // Pobeg nevarnih znakov za SQL poizvedbe
        $m = mysqli_real_escape_string($link, $m);

        $sql = "SELECT username, surname, password, type, id FROM students WHERE email = '$m'";
        $result = mysqli_query($link, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // Preverjanje gesla
            if (password_verify($p, $row['password'])) {
                $_SESSION['im'] = $row['username'];
                $_SESSION['pr'] = $row['surname'];
                $_SESSION['log'] = true;
                $_SESSION['vrsta_up'] = $row['type'];
                $_SESSION['uporabnik_id'] = $row['id'];

                $message = '<div class="success-msg">Prijava uspešna.</div>';
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'team.php';
                        }, 2000);
                      </script>";
            } else {
                $message = '<div class="error-msg">Napačno geslo.</div>';
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                      </script>";
            }
        } else {
            $message = '<div class="error-msg">Uporabnik s tem e-poštnim naslovom ne obstaja.</div>';
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                  </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERŠ LanParty2025</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <?php include 'header.php'; // Header ?>
    <main class="main-container">
        <h1 class="main-title">Prijava</h1>

        <form method="post" action="login.php">
            <div>
                <input type="email" name="mail" placeholder="Email" required autofocus>
                <div><img src="img/mail.png" alt="mail-icon"></div>
            </div>
            <div>
                <input type="password" name="geslo" id="desktop-password-field" placeholder="Geslo" required>
                <div class="pass" onclick="togglePasswordVisibility()">
                    <img src="img/eye-closed.png" id="toggle-desktop-eye" alt="eye-icon">
                </div>
            </div>
            
            <button type="submit" name="sub" value="Prijava">Prijava</button>
        </form>

        <div class="registration-link">Še nimate računa? <a href="register.php">Registriraj se zdaj.</a></div>

        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'footer.php'; // Footer ?>
    <script>

    function togglePasswordVisibility() {
        const passwordField = document.getElementById("desktop-password-field");
        const eyeIcon = document.getElementById("toggle-desktop-eye");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.src = "img/eye.png"; // Replace with the open eye image
        } else {
            passwordField.type = "password";
            eyeIcon.src = "img/eye-closed.png"; // Replace with the closed eye image
        }
    }

    </script>
</body>
</html>   