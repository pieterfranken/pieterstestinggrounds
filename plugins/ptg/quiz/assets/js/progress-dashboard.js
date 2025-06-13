// PTG Progress Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeProgressBars();
    initializeCharts();
    initializeCounters();
});

function initializeProgressBars() {
    const progressBars = document.querySelectorAll('.progress-fill');
    
    progressBars.forEach(bar => {
        const targetWidth = bar.getAttribute('data-width') || '0%';
        
        // Animate progress bar
        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 300);
    });
}

function initializeCounters() {
    const counters = document.querySelectorAll('.stat-value[data-count]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 1000; // 1 second
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
}

function initializeCharts() {
    // Simple chart implementation using CSS
    const chartContainers = document.querySelectorAll('.chart-container[data-chart]');
    
    chartContainers.forEach(container => {
        const chartType = container.getAttribute('data-chart');
        const chartData = JSON.parse(container.getAttribute('data-chart-data') || '[]');
        
        if (chartType === 'performance-trend') {
            createPerformanceTrendChart(container, chartData);
        } else if (chartType === 'quiz-type-breakdown') {
            createQuizTypeChart(container, chartData);
        }
    });
}

function createPerformanceTrendChart(container, data) {
    if (!data || data.length === 0) return;
    
    const chartHtml = `
        <div class="trend-chart">
            <div class="chart-grid">
                ${data.map((point, index) => `
                    <div class="chart-point" style="height: ${point.average_score}%; left: ${(index / (data.length - 1)) * 100}%">
                        <div class="point-tooltip">
                            <div class="tooltip-date">${formatDate(point.date)}</div>
                            <div class="tooltip-score">${Math.round(point.average_score)}%</div>
                            <div class="tooltip-attempts">${point.attempts} attempts</div>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="chart-axis">
                <span>30 days ago</span>
                <span>Today</span>
            </div>
        </div>
    `;
    
    container.innerHTML = container.innerHTML + chartHtml;
}

function createQuizTypeChart(container, data) {
    if (!data || Object.keys(data).length === 0) return;
    
    const total = Object.values(data).reduce((sum, item) => sum + item.count, 0);
    
    const chartHtml = `
        <div class="quiz-type-chart">
            ${Object.entries(data).map(([type, stats]) => {
                const percentage = (stats.count / total) * 100;
                return `
                    <div class="quiz-type-item">
                        <div class="type-info">
                            <span class="type-name">${capitalizeFirst(type)}</span>
                            <span class="type-stats">${stats.count} attempts • ${Math.round(stats.average)}% avg</span>
                        </div>
                        <div class="type-bar">
                            <div class="type-fill" style="width: ${percentage}%"></div>
                        </div>
                        <span class="type-percentage">${Math.round(percentage)}%</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
    
    container.innerHTML = container.innerHTML + chartHtml;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Add CSS for charts
const chartStyles = `
<style>
.trend-chart {
    position: relative;
    height: 200px;
    margin: 1rem 0;
}

.chart-grid {
    position: relative;
    height: 180px;
    border-bottom: 1px solid var(--border-color);
    border-left: 1px solid var(--border-color);
}

.chart-point {
    position: absolute;
    bottom: 0;
    width: 4px;
    background: var(--accent-color);
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chart-point:hover {
    background: var(--primary-color);
    transform: scaleY(1.1);
}

.point-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary-color);
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.chart-point:hover .point-tooltip {
    opacity: 1;
}

.chart-axis {
    display: flex;
    justify-content: space-between;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-color);
    opacity: 0.7;
}

.quiz-type-chart {
    margin: 1rem 0;
}

.quiz-type-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.type-info {
    flex: 1;
    min-width: 120px;
}

.type-name {
    display: block;
    font-weight: 500;
    color: var(--primary-color);
}

.type-stats {
    display: block;
    font-size: 0.8rem;
    color: var(--text-color);
    opacity: 0.7;
}

.type-bar {
    flex: 2;
    height: 8px;
    background: var(--light-gray);
    border-radius: 4px;
    overflow: hidden;
}

.type-fill {
    height: 100%;
    background: var(--accent-color);
    border-radius: 4px;
    transition: width 0.8s ease;
}

.type-percentage {
    min-width: 40px;
    text-align: right;
    font-size: 0.9rem;
    color: var(--text-color);
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', chartStyles);
