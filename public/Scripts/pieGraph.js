let papersChart;

// Mapping for office codes to display names
const officeDisplayNames = {
    'dictBulacan': 'Provincial Office Bulacan',
    'dictAurora': 'Provincial Office Aurora',
    'dictBataan': 'Provincial Office Bataan',
    'dictPampanga': 'Provincial Office Pampanga',
    'dictTarlac': 'Provincial Office Tarlac',
    'dictZambales': 'Provincial Office Zambales',
    'dictOthers': 'Others',
    'dictNe': 'Provincial Office Nueva Ecija',
    'maindoc': 'DICT Region 3 Office',
    'RdictPampanga': 'Provincial Office Pampanga',
    'RdictAurora': 'Provincial Office Aurora',
    'RdictBataan': 'Provincial Office Bataan',
    'RdictTarlac': 'Provincial Office Tarlac',
    'RdictZambales': 'Provincial Office Zambales',
    'RdictBulacan': 'Provincial Office Bulacan',
    'RdictNe': 'Provincial Office Nueva Ecija',
    'Rmaindoc': 'DICT Region 3 Office',
    'Others': 'Others',
    // Add more as needed
};

async function loadChartData() {
    try {
        const response = await fetch('/dictproj1/App/Model/graphConn/pieConn.php'); 
        const data = await response.json();

        if (data.error) {
            showError('Error: ' + data.error);
            return;
        }

        if (papersChart) {
            papersChart.destroy();
        }

        const officeTotals = {};
        data.offices.forEach(office => {
            officeTotals[office] = 0;
            data.months.forEach(month => {
                if (data.data[office] && data.data[office][month]) {
                    officeTotals[office] += data.data[office][month];
                }
            });
        });

        // Use display names for labels
        const labels = Object.keys(officeTotals).map(office => officeDisplayNames[office] || office);
        const values = Object.values(officeTotals);

        // Define colors for each office
        const officeColors = {
            'Provincial Office Bulacan': 'rgba(255, 107, 107, 0.7)',
            'Provincial Office Pampanga': 'rgba(107, 203, 119, 0.7)',
            'Provincial Office Aurora': 'rgba(77, 150, 255, 0.7)',
            'Provincial Office Bataan': 'rgba(255, 199, 95, 0.7)',
            'Provincial Office Nueva Ecija': 'rgba(132, 94, 194, 0.7)',
            'Provincial Office Tarlac': 'rgba(249, 248, 113, 0.7)',
            'Provincial Office Zambales': 'rgba(249, 132, 239, 0.7)',
            'DICT Region 3 Office': 'rgba(0, 201, 167, 0.7)',
            'Others': 'rgba(1, 31, 255, 0.7)'
        };

        // Normalize labels for consistent matching
        const normalizeLabel = label => label.trim().toLowerCase();

        const normalizedColors = {};
        for (const key in officeColors) {
            normalizedColors[normalizeLabel(key)] = officeColors[key];
        }

        const backgroundColor = labels.map(label => {
            const normalized = normalizeLabel(label);
            return normalizedColors[normalized] || 'rgba(200, 200, 200, 0.7)';
        });

        const borderColor = backgroundColor.map(color => color.replace('0.7', '1'));

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
                    borderWidth: 2,
                    hoverOffset: 10
                
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color:'white'
                        }
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
                        },
                          bodyColor: 'white',   // ← tooltip value text color
                           titleColor: 'white',  // ← tooltip title text color
                        backgroundColor: '#111' // optional: dark tooltip background
    
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
