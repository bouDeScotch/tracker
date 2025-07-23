<?php
require_once __DIR__ . '/../init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $users = loadJSONFile(DATA_PATH . 'users.json');

    foreach ($users as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            // Auth OK
            $_SESSION['user_id'] = $user['id'];

            // Génération du JWT
            $payload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'exp' => time() + 3600 // expire dans 1h
            ];
            $jwt = generateJWT($payload);

            // Tu peux renvoyer le token en AJAX ou le stocker via JS plus tard
            setcookie('auth_token', $jwt, time() + 3600, '/', '', false, true);

            header('Location: dashboard.php');
            exit;
        }
    }

    $error = 'Identifiants invalides';
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>Connexion</h1>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
