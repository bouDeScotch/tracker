<header>
    <a href="./dashboard.php">
        <div class="texts">
            <h1>Tracker</h1>
            <h2>Bonjour, <span class="username">
            <?php
                if ($user) {
                    $username = $user['firstname'] . " " . $user['lastname'];
                    $_SESSION['username'] = $username;
                } else {
                    $username = $_SESSION['username'];
                }
                echo htmlspecialchars($username);
                ?>
            </span></h2>
        </div>
    </a>
    <a href="profile.php" class="settingsLink">
        <div class="darkButton">
            Settings
        </div>
    </a>
</header>

