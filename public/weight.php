<?php
session_name('tracker_session');
session_start();
$_SESSION['username'] = 'John Doe'; // Example username, replace with actual session data
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