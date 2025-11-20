// Dashboard JavaScript with Chart.js
let trendsChart, cropChart;

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    fetch('backend/api.php?action=get_dashboard_data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardCards(data.data);
                createTrendsChart(data.data.trends);
                createCropChart(data.data.cropDistribution);
                updateRecentUpdates(data.data.notifications);
            } else {
                console.error('Failed to load dashboard data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        });
}

function updateDashboardCards(data) {
    // Update total harvests
    document.getElementById('totalHarvests').textContent = data.totalHarvests || '0';
    
    // Update active farmers
    document.getElementById('activeFarmers').textContent = data.activeFarmers || '0';
    
    // Update crops in season
    const cropsCount = data.cropsInSeason ? data.cropsInSeason.length : 0;
    document.getElementById('cropsInSeason').textContent = cropsCount;
    
    const cropsList = document.getElementById('cropsList');
    if (data.cropsInSeason && data.cropsInSeason.length > 0) {
        cropsList.textContent = data.cropsInSeason.join(', ');
    } else {
        cropsList.textContent = 'No crops in current season';
    }
    
    // Update storage capacity
    document.getElementById('storageLeft').textContent = (data.storageLeft || '0') + '%';
}

function createTrendsChart(trendsData) {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    
    // Check if there's any actual data
    const hasData = trendsData && trendsData.length > 0 && trendsData.some(item => parseFloat(item.total) > 0);
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                   'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    if (trendsChart) {
        trendsChart.destroy();
    }
    
    if (!hasData) {
        // Show empty state message instead of chart
        ctx.canvas.style.display = 'none';
        const chartContainer = ctx.canvas.parentElement;
        let emptyMessage = chartContainer.querySelector('.empty-chart-message');
        if (!emptyMessage) {
            emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-chart-message';
            emptyMessage.style.cssText = 'text-align: center; padding: 60px 20px; color: #666; font-style: italic;';
            chartContainer.appendChild(emptyMessage);
        }
        emptyMessage.innerHTML = '📊 No harvest data available<br><small>Add harvest records to see trends</small>';
        return;
    }
    
    // Show chart and hide empty message
    ctx.canvas.style.display = 'block';
    const chartContainer = ctx.canvas.parentElement;
    const emptyMessage = chartContainer.querySelector('.empty-chart-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    // Prepare data for all 12 months
    const data = new Array(12).fill(0);
    
    // Fill in actual data
    trendsData.forEach(item => {
        if (item.month >= 1 && item.month <= 12) {
            data[item.month - 1] = parseFloat(item.total) || 0;
        }
    });
    
    trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Harvest Quantity (tons)',
                data: data,
                borderColor: '#4a7c59',
                backgroundColor: 'rgba(74, 124, 89, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function createCropChart(cropData) {
    const ctx = document.getElementById('cropChart').getContext('2d');
    
    // Check if there's any actual crop data
    const hasData = cropData && cropData.length > 0 && cropData.some(item => parseFloat(item.total) > 0);
    
    if (cropChart) {
        cropChart.destroy();
    }
    
    if (!hasData) {
        // Show empty state message instead of chart
        ctx.canvas.style.display = 'none';
        const chartContainer = ctx.canvas.parentElement;
        let emptyMessage = chartContainer.querySelector('.empty-chart-message');
        if (!emptyMessage) {
            emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-chart-message';
            emptyMessage.style.cssText = 'text-align: center; padding: 60px 20px; color: #666; font-style: italic;';
            chartContainer.appendChild(emptyMessage);
        }
        emptyMessage.innerHTML = '🌾 No crop data available<br><small>Add harvest records to see crop distribution</small>';
        return;
    }
    
    // Show chart and hide empty message
    ctx.canvas.style.display = 'block';
    const chartContainer = ctx.canvas.parentElement;
    const emptyMessage = chartContainer.querySelector('.empty-chart-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    let labels = cropData.map(item => item.crop_type);
    let data = cropData.map(item => parseFloat(item.total));
    let colors = ['#4a7c59', '#6b9b7a', '#8db89a', '#aed5ba', '#cfe2da'];
    
    cropChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });
}

function updateRecentUpdates(notifications) {
    const updatesContainer = document.getElementById('recentUpdates');
    
    if (!notifications || notifications.length === 0) {
        updatesContainer.innerHTML = `
            <div class="update-item">
                <span class="update-icon">🌾</span>
                <span class="update-text">No recent updates - add some harvest data to get started</span>
            </div>
        `;
        return;
    }
    
    const updatesHTML = notifications.slice(0, 5).map(notification => {
        const icon = notification.type === 'alert' ? '⚠️' : '🌾';
        return `
            <div class="update-item">
                <span class="update-icon">${icon}</span>
                <span class="update-text">${notification.message}</span>
            </div>
        `;
    }).join('');
    
    updatesContainer.innerHTML = updatesHTML;
}
