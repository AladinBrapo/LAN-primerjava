<?php
require_once 'baza.php';

$message = "";

if (isset($_POST['sub'])) {
    // Filtriranje vhodnih podatkov
    $m = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL);
    $g = htmlspecialchars($_POST['geslo'], ENT_QUOTES, 'UTF-8');
    $i = htmlspecialchars($_POST['ime'], ENT_QUOTES, 'UTF-8');
    $p = htmlspecialchars($_POST['pri'], ENT_QUOTES, 'UTF-8');
    
    // Preverjanje veljavnosti emaila
    if (filter_var($m, FILTER_VALIDATE_EMAIL) === false) {
        $message = '<div class="error-msg">Neveljaven e-poštni naslov.</div>';
        echo "<script>
                    setTimeout(function() {
                        window.location.href = 'register.php';
                    }, 2000);
                  </script>";
    }else{

        $g2 = password_hash($g, PASSWORD_DEFAULT);
    
        // Preverjanje, če uporabnik že obstaja
        $sql = "SELECT * FROM students WHERE email = ?";
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            $message = '<div class="error-msg">Napaka pri pripravi SQL stavka: ' . mysqli_error($link) . '</div>';
            exit();
        }
        mysqli_stmt_bind_param($stmt, "s", $m);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    
        if ($result) {
            if (mysqli_num_rows($result) === 1) {
                $message = '<div class="error-msg">Pod tem emailom že imamo uporabnika. Uporabite drugega.</div>';
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'register.php';
                        }, 2000);
                      </script>";
            } else {
                mysqli_stmt_close($stmt); // Close the statement before reusing the variable
                $stmt = mysqli_prepare($link, "INSERT INTO students (username, surname, email, password, type) VALUES (?, ?, ?, ?, 'student')");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $i, $p, $m, $g2);
                    $executed = mysqli_stmt_execute($stmt);
                    if ($executed) {
                        $id_up = mysqli_insert_id($link);
    
                        $message = '<div class="success-msg">Registracija je bila uspešna.</div>';
                        echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'login.php';
                                }, 2000);
                            </script>";
                    } else {
                        $message = '<div class="error-msg">Vstavljanje neuspešno: ' . mysqli_stmt_error($stmt) . '</div>';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = '<div class="error-msg">Napaka pri pripravi SQL stavka: ' . mysqli_error($link) . '</div>';
                }
            }
        } else {
            $message = '<div class="error-msg">Napaka pri izvajanju poizvedbe: ' . mysqli_error($link) . '</div>';
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
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <?php include 'header.php'; // Header ?>
    <main class="main-container">
        <h1 class="main-title">Registracija</h1>

        <form method="post" action="register.php">
            <div>
                <input type="text" name="ime" placeholder="Name" class="" required autofocus>
            </div>
            <div>
                <input type="text" name="pri" placeholder="Surname" class="" required>
            </div>
            <div>
                <input type="email" name="mail" placeholder="Email" required>
                <div><img src="img/mail.png" alt="mail-icon"></div>
            </div>
            <div>
                <input type="password" name="geslo" id="desktop-password-field" placeholder="Geslo" required>
                <div class="pass" onclick="togglePasswordVisibility()">
                    <img src="img/eye-closed.png" id="toggle-desktop-eye" alt="eye-icon">
                </div>
            </div>

            <button type="submit" name="sub" value="Registracija">Registracija</button>
        </form>

        <div class="registration-link">Že imate račun? <a href="login.php">Prijavite se.</a></div>

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