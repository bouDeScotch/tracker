<?php
session_name('tracker_session');
session_start();

if (!isset($_SESSION["email"])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/helpers.php';

$usersData = loadJSONFile(__DIR__ . '/../data/users.json');
$email = $_SESSION['email'];
$currentUser = null;
$currentUserKey = null;

foreach ($usersData as $key => $user) {
    if ($user['email'] === $email) {
        $currentUser = $user;
        $currentUserKey = $key;
        break;
    }
}

if (!$currentUser) {
    // Should never happen
    exit('User not found. ' . $_SESSION['email']);
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $height = (int)($_POST['height'] ?? 0);
    $birthdate = $_POST['birthdate'] ?? '';

    if (strlen($firstname) < 2 || strlen($firstname) > 15) {
        $error .= 'Error : First name should be between 2 and 15 characters long. </br>';
    } else {
        $usersData[$currentUserKey]['firstname'] = $firstname;
    }

    if (strlen($lastname) < 2 || strlen($lastname) > 15) {
        $error .= 'Error : Last name should be between 2 and 15 characters long. </br>';
    } else {
        $usersData[$currentUserKey]['lastname'] = $lastname;
    }

    if ($height < 100 || $height > 280) {
        $error .= 'Error : Height should be between 100 and 280cm. </br>';
    } else {
        $usersData[$currentUserKey]["height"] = $height;
    }

    $birthDateDateTime = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (! $birthDateDateTime || $birthDateDateTime->format('Y-m-d') !== $birthdate) {
        $error .= 'Error : Date is invalid. </br>';
    } else {
        $now = new DateTime();
        $age = $now->diff($birthDateDateTime)->y;
        if ($age <= 10 && $age >= 120) {
            $error .= 'Error : The birth date entered makes you below 10 or above 120 years old. </br>';
        } else {
            $usersData[$currentUserKey]["birthdate"] = $birthdate;
        }
    }

    if (saveJSONFile(__DIR__ . '/../data/users.json', $usersData)) {
        $success = true;
    } else {
        $error .= "Error : Saving to file failed.";
    }
}

$currentUser = $usersData[$currentUserKey];
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
<h1>Modifier mon profil</h1>

<?php if ($error): ?>
  <p style="color:red;"><?= $error ?></p>
<?php elseif ($success): ?>
  <p style="color:green;">Profil mis à jour avec succès. Redirecting to the dashboard, please wait...</p>
  <script>
    setTimeout(function() {
        window.location.href = "dashboard.php";
    }, 2000);
  </script>
<?php endif; ?>

<form method="post">
  <label>First name : <input type="text" name="firstname" value="<?= htmlspecialchars($currentUser['firstname'] ?? '') ?>"></label><br>
  <label>Last name : <input type="text" name="lastname" value="<?= htmlspecialchars($currentUser['lastname'] ?? '') ?>"></label><br>
  <label>Height (cm) : <input type="number" name="height" value="<?= htmlspecialchars($currentUser['height'] ?? '') ?>"></label><br>
  <label>Birthdate : <input type="date" name="birthdate" value="<?= htmlspecialchars($currentUser['birthdate'] ?? '') ?>"></label><br>
  <button type="submit" class="darkButton">Save</button>
</form>
</body>
</html>