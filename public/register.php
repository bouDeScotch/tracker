<?php
session_name('tracker_session');
session_start();

require_once __DIR__ . '/../init.php';

// Before asking anything, instantly redirect to dashboard if already connected
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

function handlePostRequest(): array|string {
    if (! isset($_POST['email'])) {
        return ["error" => "Email is not set"];
    }
    $email = trim($_POST['email']);

    if (! isset($_POST['password'])) {
        return ["error" => "Password is not set"];
    }
    $password = $_POST['password'];

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["error" => "Invalid email format"];
    }

    $usersData = loadJSONFile(__DIR__ . '/../data/users.json');
    foreach ($usersData as $user) {
        if ($user['email'] === $email) {
            return ["error" => "Email already exists"];
        }
    }

    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
    $newUser = [
        'email' => $email,
        'password' => $hashedPwd,
        'created_at' => date('c'),
        'id' => count($usersData) + 1 // Simple ID generation
    ];
    $usersData[] = $newUser;
    
    $_SESSION['email'] = $email;
    $_SESSION['user_id'] = $newUser['id'];

    $payload = [
        'id' => $newUser['id'],
        'email' => $newUser['email'],
        'exp' => time() + 3600 // expire dans 1h
    ];
    $jwt = generateJWT($payload);

    setcookie('auth_token', $jwt, time() + 3600, '/', '', false, true);

    if (saveJSONFile(__DIR__ . '/../data/users.json', $usersData)) {
        return $newUser;
    } else {
        return ["error" => "Failed to save user data"];
    }
}


$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handlePostRequest();
    if (!isset($result['error'])) {
        header("Location: profile.php");
        exit;
    }
    $error = $result['error'];
} else {
    // If not a POST request, just show the registration form
    $error = "";
    $success = false;
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