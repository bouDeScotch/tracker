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
            <h2>Bonjour, <span class="username">
                <?php
                session_name('tracker_session');
                session_start();
                $_SESSION['username'] = 'John Doe'; // Example username, replace with actual session data
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

    <div class="buttons">
        <div class="roundButton addButton"></div>
        <a href="">
            <div class="roundButton mealButton"></div>
        </a>
        <a href="">
            <div class="roundButton workoutButton"></div>
        </a>
        <a href="">
            <div class="roundButton weightButton"></div>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Chart.js script -->
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
                    },
                    display: false
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    },
                    display: false
                }
            }
        }
        });
    });


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

    <script src="/tracker/public/assets/addButton.js"></script>
</body>
</html>