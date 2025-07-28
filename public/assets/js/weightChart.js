// assets/js/weightChart.js

function loadWeightChart(canvasId, apiUrl) {
  const ctx = document.getElementById(canvasId).getContext('2d');

  fetch(apiUrl)
    .then(res => res.json())
    .then(data => {
      const sorted = data.data.sort((a, b) => new Date(a.date) - new Date(b.date));
      const points = sorted.map(entry => ({
          x: entry.date,  // format ISO (YYYY-MM-DD)
          y: entry.weight
      }));

      new Chart(ctx, {
        type: 'line',
        data: {
          datasets: [{
            label: 'Weight (kg)',
            data: points,
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
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: false,
              grid: { display: false },
              ticks: { display: false },
              display: false
            },
            x: {
                type: "time",
                time: {
                    unit: 'day',
                    tooltipFormat: 'd MMM',
                },
                grid: { display: false },
              ticks: { display: false },
              display: false
            }
          }
        }
      });
    });
}

