const moneyFormatter = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
});

const chartPalette = ['#f59e0b', '#38bdf8', '#34d399', '#f43f5e', '#a78bfa', '#14b8a6'];

function chartCanvas(id) {
    return document.getElementById(id);
}

function renderAdminCharts(data) {
    if (typeof Chart === 'undefined') return;

    const revenue = data.revenue_vs_orders || [];
    const topProducts = data.top_products || [];
    const categories = data.category_distribution || [];

    const revenueCanvas = chartCanvas('admin-revenue-orders-chart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            data: {
                labels: revenue.map(item => item.label),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Ingresos',
                        data: revenue.map(item => Number(item.revenue || 0)),
                        backgroundColor: 'rgba(245, 158, 11, 0.35)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Pedidos',
                        data: revenue.map(item => Number(item.orders || 0)),
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.15)',
                        tension: 0.35,
                        fill: false,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#cbd5e1' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.08)' } },
                    y: { ticks: { color: '#94a3b8', callback: value => moneyFormatter.format(value) }, grid: { color: 'rgba(148, 163, 184, 0.08)' } },
                    y1: { position: 'right', ticks: { color: '#94a3b8' }, grid: { drawOnChartArea: false } },
                },
            },
        });
    }

    const topCanvas = chartCanvas('admin-top-products-chart');
    if (topCanvas) {
        new Chart(topCanvas, {
            type: 'bar',
            data: {
                labels: topProducts.map(item => item.label),
                datasets: [{ label: 'Unidades vendidas', data: topProducts.map(item => Number(item.quantity || 0)), backgroundColor: '#34d399' }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#cbd5e1' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.08)' } },
                },
            },
        });
    }

    const categoryCanvas = chartCanvas('admin-category-distribution-chart');
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'pie',
            data: {
                labels: categories.map(item => item.label),
                datasets: [{ data: categories.map(item => Number(item.total || 0)), backgroundColor: chartPalette }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1' } } },
            },
        });
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!document.getElementById('admin-dashboard-stats')) return;

    try {
        const response = await fetch(window.buildAppUrl('api/stats_admin'), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const payload = await response.json();
        if (!response.ok || !payload.exito) throw new Error(payload.mensaje || 'No se pudieron cargar las estadísticas.');
        renderAdminCharts(payload.data || {});
    } catch (error) {
        console.error('Admin stats error:', error);
        const container = document.getElementById('admin-dashboard-stats-error');
        if (container) container.textContent = 'No se pudieron cargar las gráficas.';
    }
});
