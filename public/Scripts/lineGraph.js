

fetch('/dictproj1/App/Model/graphConn/lineConn.php') 
  .then(response => response.json())
  .then(data => {
    const labels = Object.keys(data);   
    console.log("lay bells",labels)
    const values = Object.values(data); 
    console.log("val use" ,values)
    const ctx = document.getElementById('myChart2').getContext('2d');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Sent Documents per Month',
          data: values,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(96, 165, 250, 0.2)',
          fill: true,
          tension: 0.3,
          pointBackgroundColor: '#1e3a8a'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
        
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  })
  .catch(error => console.error('Line Chart Error:', error));
