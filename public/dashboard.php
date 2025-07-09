<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/tracker/public/assets/style.css">
    <title>Macros tracker</title>
</head>
<body>
    <header>
        <div class="texts">
            <h1>Tracker</h1>
            <h2>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        </div>
        <div class="settingsButton">
            <a href="settings.php" class="settingsLink">Settings</a>
        </div>
    </header>
    <div class="summary">
        <h2>Summary</h2>
        <ul class="cardsList">
            <li class="card single">
                <h3>Energy : 1875 / 2300 kcal</h3>
                <div class="progressBar notFull" data-progress="81.52"></div>
            </li>
            <li class="card single">
                <h3>Protein : 150 / 120 g</h3>
                <div class="progressBar full" data-progress="125"></div>
            </li>
            <li class="card array">
                <h3>Mass : 71.0 kg</h3>
                <canvas id="weightChart" width="300" height="200"></canvas>
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

    <div class="roundButton addButton">
        <a href="">
            <div class="roundButton"></div>
        </a><a href="">
            <div class="roundButton"></div>
        </a><a href="">
            <div class="roundButton"></div>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('weightChart').getContext('2d');

    fetch('/tracker/api/getWeight.php')
    .then(res => res.json())
    .then(data => {
        const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,     // ["01/07", "02/07", ...]
            datasets: [{
                label: 'Poids (kg)',
                data: data.weights,   // [72, 71.8, 71.5, ...]
                borderColor: '#3FB839',
                pointBackgroundColor: '#00000000',
                pointBorderColor: '#3FB839',
                borderWidth: 4,
                pointBorderWidth: 4,
                fill: false,
                tension: 0.6,
                pointRadius: 9
                }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {display: false},
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    }
                }
            }
        }
        });
    });


    </script>
</body>
</html>