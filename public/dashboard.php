<?php
session_name('tracker_session');
session_start();
error_log('Session ID: ' . session_id());

if (! isset($_SESSION['user_id'])) {
    //echo('Session user_id not set, redirecting to register.php');
    header('Location: register.php');
    exit();
}

require_once __DIR__ . '/../init.php';
if (!isset($_SESSION['email'])) {
    //error_log('Session email not set, redirecting to register.php');
    header('Location: register.php');
    exit();
}

$user = getUserInfo($_SESSION['email']);
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
    <div class="summary">
        <h2>Summary</h2>
        <ul class="cardsList">
            <li class="card single">
                <h3>Energy : 1875 / 2300 kcal</h3>
                <div class="progressBar notFull" data-progress="81.52"></div>
            </li>
            <li class="card single">
                <h3>Protein : 150 / 120 g (+ 30 g)</h3>
                <div class="progressBar full" data-progress="125"></div>
            </li>
            <li class="card array">
            <h3>Mass : <?php 
            $response = loadJSONFile(DATA_PATH . "/weights.json");
            if (!isset($response[$user["email"]])) {
                echo "No data, <a href='./weights.php'>log a weight</a> before !";
            } else {
                echo end($response[$user["email"]])["weight"] . " kg";
            }
            ?></h3>
                <div id="graphContainer">
                    <canvas id="weightChart"></canvas>
                </div>
            </li>
        </ul>
    </div>
    <div class="actionsHistory">
        <h2>Last actions</h2>
        <ul class="actionsList">
            <li class="actionCard">
                <div class="texts">
                    <span class="actionType">Logged a meal</span>
                    <span class="date">09/07/2025</span>
                </div>

                <div class="actionsButtons">
                    <button class="editButton"></button>
                    <button class="deleteButton"></button>
                </div>
            </li>
            <li class="actionCard">
                <div class="texts">
                    <span class="actionType">Logged a weight</span>
                    <span class="date">09/07/2025</span>
                </div>

                <div class="actionsButtons">
                    <button class="editButton"></button>
                    <button class="deleteButton"></button>
                </div>
            </li>
            <li class="actionCard">
                <div class="texts">
                    <span class="actionType">Logged a workout</span>
                    <span class="date">09/07/2025</span>
                </div>

                <div class="actionsButtons">
                    <button class="editButton"></button>
                    <button class="deleteButton"></button>
                </div>
            </li>
            <li class="actionCard">
                <div class="texts">
                    <span class="actionType">Logged a meal</span>
                    <span class="date">09/07/2025</span>
                </div>

                <div class="actionsButtons">
                    <button class="editButton"></button>
                    <button class="deleteButton"></button>
                </div>
            </li>
        </ul>
    </div>

    <div class="buttons">
        <div class="roundButton addButton"></div>
        <a href="./meals.php">
            <div class="roundButton mealButton"></div>
        </a>
        <a href="./workouts.php">
            <div class="roundButton workoutButton"></div>
        </a>
        <a href="./weight.php">
            <div class="roundButton weightButton"></div>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <!-- Chart.js script -->
    <script src="./assets/js/weightChart.js"> </script>
    <script>
        loadWeightChart('weightChart', '../api/getWeight.php');
    </script>
    <!-- Progress bars script -->
                
    <script>
        document.querySelectorAll('.progressBar').forEach(bar => {
            const progress = parseFloat(bar.dataset.progress);
            const clampedProgress = Math.min(progress, 100);
            bar.style.setProperty('--progress', `${clampedProgress}%`);
            bar.classList.toggle('full', clampedProgress === 100);
            bar.classList.toggle('notFull', clampedProgress < 100);
            bar.style.position = 'relative';
            const inner = document.createElement('div');
            inner.style.position = 'absolute';
            inner.style.height = '100%';
            inner.style.left = '0';
            inner.style.top = '0';
            inner.style.backgroundColor = clampedProgress === 100 ? 'var(--negativeFill)' : 'var(--positiveFill)';
            inner.style.transition = 'width 0.3s ease';
            inner.style.borderRadius = 'inherit';
            bar.appendChild(inner);
            inner.style.width = clampedProgress + "%";
        });
    </script>

    <script src="assets/addButton.js"></script>
</body>
</html>
