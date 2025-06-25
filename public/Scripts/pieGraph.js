let papersChart;

// Function to load chart data from database
async function loadChartData() {
    try {
        const response = await fetch('/dictproj1/App/Model/graphConn/pieConn.php'); 
        const data = await response.json();
        
        if (data.error) {
            showError('Error: ' + data.error);
            return;
        }

        // Destroy existing chart if it exists
        if (papersChart) {
            papersChart.destroy();
        }

        // Prepare data for pie chart (total papers per office)
        const officeTotals = {};
        data.offices.forEach(office => {
            officeTotals[office] = 0;
            data.months.forEach(month => {
                if (data.data[office] && data.data[office][month]) {
                    officeTotals[office] += data.data[office][month];
                }
            });
        });

        const labels = Object.keys(officeTotals);
        const values = Object.values(officeTotals);

        const officeColors = {
            'Provincial Office 1': 'rgba(104, 127, 229, 0.7)',
            'Provincial Office 2': 'rgba(252, 216, 205, 0.7)',
            'Provincial Office 3': 'rgba(255, 206, 86, 0.7)',
            'Provincial Office 4': 'rgba(75, 192, 192, 0.7)',
            'Provincial Office 5': 'rgba(153, 102, 255, 0.7)',
            'Provincial Office 6': 'rgba(255, 159, 64, 0.7)',
            'Others': 'rgba(255, 17, 255, 0.7)'
           

        };
        
       const backgroundColor = labels.map(label => officeColors[label] || 'rgba(200, 200, 200, 0.7)');
const borderColor = backgroundColor.map(color => color.replace('0.7', '1'));
        // Create custom legend
        createCustomLegend(labels, backgroundColor);

        const ctx = document.getElementById('papersChart').getContext('2d');
        papersChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Papers Sent',
                    data: values,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });

        hideError();

    } catch (error) {
        console.error('Error loading chart data:', error);
        showError('Error loading data');
    }
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function hideError() {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.style.display = 'none';
}

function createCustomLegend(labels, colors) {
    const legendContainer = document.getElementById('chartLegend');
    legendContainer.innerHTML = '';
    
    labels.forEach((label, index) => {
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        
        const dot = document.createElement('div');
        dot.className = 'legend-dot';
        dot.style.backgroundColor = colors[index];
        dot.style.setProperty('background-color', colors[index], 'important');
        
        const text = document.createElement('span');
        text.className = 'legend-text';
        text.textContent = label;
        
        legendItem.appendChild(dot);
        legendItem.appendChild(text);
        legendContainer.appendChild(legendItem);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadChartData();
});
