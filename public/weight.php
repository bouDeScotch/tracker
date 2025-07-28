<?php
session_name('tracker_session');
session_start();

// Checking if user connected, if not redirect him to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../init.php';
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
    <?php include "./header.php" ?>
    <div class="weight-tracker-page">
        <form action="../api/log_weight.php" method="POST">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" value="<?php echo date('Y-m-d'); ?>" id="date" name="date" required/>
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" step=0.1 id="weight" name="weight" required />
            </div>
            <div class="form-group">
                <label for="note">Note (optional)</label>
                <textarea id="note" name="note" rows="2"></textarea> 
            </div>
            <button type="submit" class="btn">Ajouter</button> 
        </form>
        <div class="weight-graph">
            <div id="graphContainer">
                <canvas id="weightChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <!-- Chart.js script -->
    <script src="./assets/js/weightChart.js"> </script>
    <script>
        loadWeightChart('weightChart', '../api/getWeight.php');
    </script>
</body>
</html>
