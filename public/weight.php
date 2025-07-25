<?php
session_name('tracker_session');
session_start();

// Checking if user connected, if not redirect him to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '../init.php';
$userInfo = getUserInfo($_SESSION['email']);
if ($userInfo === null) {
    header('Location: login.php');
    exit;
}
$_SESSION['username'] = $userInfo["firstname"] . " " . $userInfo["lastname"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <title>Macros tracker</title>
</head>
<body>
    <header>
        <div class="texts">
            <h1>Tracker</h1>
            <h2>Bonjour, <span class="username">
                <?php
                echo htmlspecialchars($_SESSION['username']); 
                ?>
            </span></h2>
        </div>
        <a href="settings.php" class="settingsLink">
            <div class="settingsButton">
                Settings
            </div>
        </a>
    </header>
</body>
</html>
