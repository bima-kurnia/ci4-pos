<?= $this->extend('layouts/main') ?>
<?= $this->section('head') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb + controls -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="<?= base_url('analytics') ?>" class="hover:text-sky-600">Analytics</a>
        <span>›</span>
        <span class="font-semibold text-slate-800">Sales Forecast</span>
    </div>
    <div class="flex gap-2">
        <a href="<?= base_url('analytics/sales-forecast?days=7') ?>"
           class="btn btn-sm <?= $forecast_days == 7 ? 'btn-primary' : 'btn-outline' ?>">7 Days</a>
        <a href="<?= base_url('analytics/sales-forecast?days=30') ?>"
           class="btn btn-sm <?= $forecast_days == 30 ? 'btn-primary' : 'btn-outline' ?>">30 Days</a>
        <a href="<?= base_url('analytics/sales-forecast?days=' . $forecast_days . '&refresh=1') ?>"
           class="btn btn-sm btn-ghost gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error"><span><?= esc($error) ?></span></div>
<?php elseif (!empty($result)): ?>

<!-- Cache badge -->
<?php if (!empty($from_cache)): ?>
<div class="alert alert-info py-2 mb-4 text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Showing cached result from <?= esc($result['_cached_at'] ?? '') ?>.
    Expires <?= esc($result['_expires_at'] ?? '') ?>.
    <a href="?days=<?= $forecast_days ?>&refresh=1" class="underline ml-1 font-semibold">Refresh now</a>
</div>
<?php endif; ?>

<!-- Summary stats -->
<?php
$trend      = $result['trend']          ?? 'stable';
$trendPct   = $result['trend_pct']      ?? 0;
$avgRevenue = $result['avg_daily_revenue'] ?? 0;
$predictions= $result['predictions']    ?? [];
$totalForecast = array_sum(array_column($predictions, 'predicted_revenue'));
$trendColors = ['growing' => 'text-emerald-600', 'declining' => 'text-red-500', 'stable' => 'text-amber-500'];
$trendIcons  = ['growing' => '↑', 'declining' => '↓', 'stable' => '→'];
?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Avg Daily Revenue</p>
        <p class="text-xl font-extrabold text-slate-800">Rp <?= number_format($avgRevenue, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-0.5">Last 90 days average</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Forecast Total</p>
        <p class="text-xl font-extrabold text-sky-700">Rp <?= number_format($totalForecast, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-0.5">Next <?= $forecast_days ?> days</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Trend</p>
        <p class="text-xl font-extrabold <?= $trendColors[$trend] ?? 'text-slate-800' ?>">
            <?= $trendIcons[$trend] ?? '' ?> <?= ucfirst($trend) ?>
        </p>
        <p class="text-xs text-slate-400 mt-0.5"><?= ($trendPct >= 0 ? '+' : '') . number_format($trendPct, 1) ?>% vs recent</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Model</p>
        <p class="text-sm font-bold text-slate-700">LinearReg + WMA</p>
        <p class="text-xs text-slate-400 mt-0.5"><?= esc($result['model_used'] ?? '') ?></p>
    </div>
</div>

<!-- Chart -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
    <h3 class="font-bold text-slate-800 mb-4">Revenue Forecast — Next <?= $forecast_days ?> Days</h3>
    <div class="relative h-72">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<!-- Prediction table -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-slate-800">Day-by-Day Predictions</h3>
        <span class="text-xs text-slate-400"><?= count($predictions) ?> days</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th class="text-right">Predicted Revenue</th>
                    <th class="text-right">Lower Bound</th>
                    <th class="text-right">Upper Bound</th>
                    <th class="text-center">Predicted Trx</th>
                    <th class="text-center">Confidence</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($predictions as $p): ?>
                <tr class="hover">
                    <td class="font-mono text-sm"><?= esc($p['date']) ?></td>
                    <td class="text-xs text-slate-400"><?= date('D', strtotime($p['date'])) ?></td>
                    <td class="text-right font-bold text-sky-700">
                        Rp <?= number_format($p['predicted_revenue'], 0, ',', '.') ?>
                    </td>
                    <td class="text-right text-xs text-slate-400">
                        Rp <?= number_format($p['lower_bound'], 0, ',', '.') ?>
                    </td>
                    <td class="text-right text-xs text-slate-400">
                        Rp <?= number_format($p['upper_bound'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center"><?= (int)$p['predicted_count'] ?></td>
                    <td class="text-center">
                        <?php $conf = (float)$p['confidence']; ?>
                        <div class="flex items-center gap-1.5 justify-center">
                            <progress class="progress progress-primary w-12 h-1.5"
                                      value="<?= $conf * 100 ?>" max="100"></progress>
                            <span class="text-xs"><?= round($conf * 100) ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const predictions = <?= json_encode($predictions) ?>;

const labels    = predictions.map(p => p.date);
const revenue   = predictions.map(p => p.predicted_revenue);
const lower     = predictions.map(p => p.lower_bound);
const upper     = predictions.map(p => p.upper_bound);

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label:           'Predicted Revenue',
                data:            revenue,
                borderColor:     '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.08)',
                borderWidth:     2.5,
                pointRadius:     3,
                pointHoverRadius:6,
                fill:            true,
                tension:         0.4,
            },
            {
                label:           'Upper Bound',
                data:            upper,
                borderColor:     'rgba(14,165,233,0.2)',
                backgroundColor: 'transparent',
                borderWidth:     1,
                borderDash:      [5,5],
                pointRadius:     0,
                fill:            false,
            },
            {
                label:           'Lower Bound',
                data:            lower,
                borderColor:     'rgba(14,165,233,0.2)',
                backgroundColor: 'rgba(14,165,233,0.05)',
                borderWidth:     1,
                borderDash:      [5,5],
                pointRadius:     0,
                fill:            '-1', // fill between upper and lower
            },
        ],
    },
    options: {
        responsive:         true,
        maintainAspectRatio:false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const val = ctx.parsed.y;
                        return `${ctx.dataset.label}: Rp ${val.toLocaleString('id-ID')}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid:  { display: false },
                ticks: { maxTicksLimit: 10, font: { size: 11 } },
            },
            y: {
                grid:  { color: 'rgba(0,0,0,0.04)' },
                ticks: {
                    font: { size: 11 },
                    callback: val => 'Rp ' + val.toLocaleString('id-ID'),
                },
            },
        },
    },
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
