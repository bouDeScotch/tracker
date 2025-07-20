<?php
session_name('tracker_session');
session_start();

require_once __DIR__ . '/../lib/helpers.php';

// Before asking anything, instantly redirect to dashboard if already connected
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

function handlePostRequest(): bool|string {
    if (! isset($_POST['email'])) {
        return 'Error : email is not set';
    }
    $email = trim($_POST['email']);

    if (! isset($_POST['password'])) {
        return 'Error : password is not set';
    }
    $password = $_POST['password'];

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Error : email is not valid';
    }

    $usersData = loadJSONFile(__DIR__ . '/../data/users.json');
    foreach ($usersData as $user) {
        if ($user['email'] === $email) {
            return 'Error : this email is already used';
        }
    }

    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
    $usersData[] = [
        'email' => $email,
        'password' => $hashedPwd,
        'created_at' => date('c'),
        'id' => count($usersData) + 1 // Simple ID generation
    ];
    
    $_SESSION['email'] = $email;

    return saveJSONFile(__DIR__ . '/../data/users.json', $usersData);
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Here we handle the case where the form is posted
    $result = handlePostRequest();

    if ($result === true) {
        // The result is true, we can login the user without needing to ask for credentials again
        $payload = [
            'id' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + 3600 // expire dans 1h
        ];
        $jwt = generateJWT($payload);

        setcookie('auth_token', $jwt, time() + 3600, '/', '', false, true);

        $success = true;
        header("Location: profile.php");
        exit;
    } else {
        $error = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/styleRegister.css">
    <title>Tracker | Register</title>
</head>
<body>
<h1>Inscription</h1>
<?php if ($error): ?>
  <p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>
<form method="post">
  <label>Email: <input type="email" name="email" required class="darkButton"></label>
  <label>Mot de passe: <input type="password" name="password" required class="darkButton"></label>
  <button type="submit" class="darkButton">S’inscrire</button>
</form>
</body>
</html>