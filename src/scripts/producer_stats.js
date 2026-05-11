const moneyFormatter = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
});

const chartPalette = ['#f97316', '#3b82f6', '#22c55e', '#a855f7', '#eab308', '#ef4444'];

function renderProducerCharts(data) {
    if (typeof Chart === 'undefined') return;

    const revenue = data.revenue_vs_sales || [];
    const topProducts = data.top_products || [];
    const statuses = data.shipping_status || [];

    const revenueCanvas = document.getElementById('producer-revenue-sales-chart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: revenue.map(item => item.label),
                datasets: [{
                    label: 'Ingresos últimos 30 días',
                    data: revenue.map(item => Number(item.revenue || 0)),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.18)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#374151' } } },
                scales: {
                    x: { ticks: { color: '#6b7280', maxTicksLimit: 8 }, grid: { display: false } },
                    y: { ticks: { color: '#6b7280', callback: value => moneyFormatter.format(value) }, grid: { color: 'rgba(107, 114, 128, 0.12)' } },
                },
            },
        });
    }

    const topCanvas = document.getElementById('producer-top-products-chart');
    if (topCanvas) {
        new Chart(topCanvas, {
            type: 'bar',
            data: {
                labels: topProducts.map(item => item.label),
                datasets: [{ label: 'Unidades', data: topProducts.map(item => Number(item.quantity || 0)), backgroundColor: '#3b82f6' }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#374151' } } },
                scales: {
                    x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(107, 114, 128, 0.12)' } },
                    y: { ticks: { color: '#6b7280' }, grid: { display: false } },
                },
            },
        });
    }


}

document.addEventListener('DOMContentLoaded', async () => {
    if (!document.getElementById('producer-dashboard-stats')) return;

    try {
        const response = await fetch(window.buildAppUrl('api/stats_producer'), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const payload = await response.json();
        if (!response.ok || !payload.exito) throw new Error(payload.mensaje || 'No se pudieron cargar las estadísticas.');
        renderProducerCharts(payload.data || {});
    } catch (error) {
        console.error('Producer stats error:', error);
        const container = document.getElementById('producer-dashboard-stats-error');
        if (container) container.textContent = 'No se pudieron cargar las gráficas.';
    }
});
