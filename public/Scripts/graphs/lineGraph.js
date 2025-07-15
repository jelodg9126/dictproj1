

fetch('/dictproj1/App/Model/graphConn/lineConn.php')
  .then(response => response.json())
  .then(data => {
    const labels = Object.keys(data);
    console.log("lay bells", labels)
    const values = Object.values(data);
    console.log("val use", values)
    const ctx = document.getElementById('myChart2').getContext('2d');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Sent Documents per Month',
          data: values,
          borderColor: '#3b82f6',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#1e3a8a',
          pointRadius: 5,
          pointHoverRadius: 7

        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: 'white' // legend text color
            }
          },
        },
        scales: {
          x: {
            ticks: {
              color: 'white' // x-axis label color
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)' // light x-grid lines
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              color: 'white' // y-axis label color
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.1)' // light y-grid lines
            }
          }
        }
      }
    });
  })
  .catch(error => console.error('Line Chart Error:', error));
