fetch('/dictproj1/App/Model/graphConn/barConn.php')
  .then(response => response.json())
  .then(data => {
    console.log("Fetched data:", data); 

    const labels = ['In-Person', 'Courier']; 
    const modeDel = labels.map(label => data[label] || 0); 

    const ctx = document.getElementById('myChart').getContext('2d');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: '# of Senders',
          data: modeDel,
          backgroundColor: ['#10b981', '#f59e0b'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  })
  .catch(error => console.error("Chart fetch error:", error));
