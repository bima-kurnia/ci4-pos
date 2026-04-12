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
        <span class="font-semibold text-slate-800">Product Demand Forecast</span>
    </div>
    <div class="flex gap-2">
        <a href="<?= base_url('analytics/product-forecast?days=7') ?>"
           class="btn btn-sm <?= ($forecast_days ?? 30) == 7 ? 'btn-primary' : 'btn-outline' ?>">7 Days</a>
        <a href="<?= base_url('analytics/product-forecast?days=30') ?>"
           class="btn btn-sm <?= ($forecast_days ?? 30) == 30 ? 'btn-primary' : 'btn-outline' ?>">30 Days</a>
        <a href="?days=<?= $forecast_days ?? 30 ?>&refresh=1" class="btn btn-sm btn-ghost gap-1">
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

<?php if (!empty($from_cache)): ?>
<div class="alert alert-info py-2 mb-4 text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Cached result from <?= esc($result['_cached_at'] ?? '') ?>.
    <a href="?days=<?= $forecast_days ?>&refresh=1" class="underline ml-1 font-semibold">Refresh now</a>
</div>
<?php endif; ?>

<?php
$rankings   = $result['rankings'] ?? [];
$top5       = array_slice($rankings, 0, 5);
$trendBadge = [
    'rising'  => '<span class="badge badge-success badge-sm gap-1">↑ Rising</span>',
    'stable'  => '<span class="badge badge-warning badge-sm gap-1">→ Stable</span>',
    'falling' => '<span class="badge badge-error badge-sm gap-1">↓ Falling</span>',
];
$colors = ['#0ea5e9','#8b5cf6','#10b981','#f59e0b','#ef4444'];
?>

<!-- Top 3 podium cards -->
<div class="grid grid-cols-3 gap-4 mb-5">
    <?php foreach (array_slice($top5, 0, 3) as $i => $p):
        $medals = ['🥇','🥈','🥉'];
        $podiumBg = ['from-amber-50 to-yellow-50','from-slate-50 to-gray-100','from-orange-50 to-amber-50'];
    ?>
    <div class="bg-gradient-to-br <?= $podiumBg[$i] ?> rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
        <div class="text-3xl mb-2"><?= $medals[$i] ?></div>
        <p class="font-bold text-slate-800 text-sm leading-tight mb-1"><?= esc($p['name']) ?></p>
        <p class="text-2xl font-extrabold text-sky-700 mb-1"><?= number_format($p['predicted_qty']) ?></p>
        <p class="text-xs text-slate-400">predicted units</p>
        <div class="mt-2"><?= $trendBadge[$p['trend']] ?? '' ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    <!-- Bar chart: top 10 -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <h3 class="font-bold text-slate-800 mb-4">Top 10 by Predicted Demand</h3>
        <div class="relative h-72">
            <canvas id="productChart"></canvas>
        </div>
    </div>

    <!-- Trend breakdown -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <h3 class="font-bold text-slate-800 mb-4">Trend Breakdown</h3>
        <?php
        $rising  = count(array_filter($rankings, fn($r) => $r['trend'] === 'rising'));
        $stable  = count(array_filter($rankings, fn($r) => $r['trend'] === 'stable'));
        $falling = count(array_filter($rankings, fn($r) => $r['trend'] === 'falling'));
        $total   = count($rankings);
        ?>
        <div class="space-y-4 mt-4">
            <?php foreach ([
                ['label'=>'Rising',  'count'=>$rising,  'color'=>'bg-emerald-500', 'badge'=>'badge-success'],
                ['label'=>'Stable',  'count'=>$stable,  'color'=>'bg-amber-400',   'badge'=>'badge-warning'],
                ['label'=>'Falling', 'count'=>$falling, 'color'=>'bg-red-500',     'badge'=>'badge-error'],
            ] as $t):
                $pct = $total > 0 ? round($t['count'] / $total * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-semibold text-slate-700"><?= $t['label'] ?></span>
                    <span class="badge <?= $t['badge'] ?> badge-sm"><?= $t['count'] ?> products</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="<?= $t['color'] ?> h-2.5 rounded-full transition-all duration-500"
                         style="width: <?= $pct ?>%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-0.5 text-right"><?= $pct ?>%</p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 p-4 bg-slate-50 rounded-xl">
            <p class="text-xs font-semibold text-slate-500 mb-2">💡 Insight</p>
            <?php if ($rising > $falling): ?>
            <p class="text-sm text-slate-600">
                <strong><?= $rising ?> products</strong> are on an upward trend.
                Consider stocking up on <?= esc($rankings[0]['name'] ?? '—') ?> to meet demand.
            </p>
            <?php elseif ($falling > $rising): ?>
            <p class="text-sm text-slate-600">
                <strong><?= $falling ?> products</strong> are declining.
                Review pricing or promotion strategy for falling items.
            </p>
            <?php else: ?>
            <p class="text-sm text-slate-600">Demand is mostly stable. Maintain current stock levels.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Full ranking table -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-bold text-slate-800">Full Product Ranking — Next <?= $forecast_days ?> Days</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-center">Rank</th>
                    <th>Product</th>
                    <th class="text-right">Predicted Units</th>
                    <th class="text-right">Predicted Revenue</th>
                    <th class="text-center">Trend</th>
                    <th class="text-center">Δ %</th>
                    <th class="text-center">Confidence</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankings as $p): ?>
                <tr class="hover">
                    <td class="text-center">
                        <span class="font-bold text-slate-600 text-sm">#<?= $p['rank'] ?></span>
                    </td>
                    <td class="font-semibold text-slate-800"><?= esc($p['name']) ?></td>
                    <td class="text-right font-bold text-sky-700"><?= number_format($p['predicted_qty']) ?></td>
                    <td class="text-right font-semibold">Rp <?= number_format($p['predicted_revenue'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= $trendBadge[$p['trend']] ?? '' ?></td>
                    <td class="text-center text-xs font-mono <?= $p['trend_pct'] >= 0 ? 'text-emerald-600' : 'text-red-500' ?>">
                        <?= ($p['trend_pct'] >= 0 ? '+' : '') . number_format($p['trend_pct'], 1) ?>%
                    </td>
                    <td class="text-center">
                        <div class="flex items-center gap-1 justify-center">
                            <progress class="progress progress-primary w-10 h-1.5"
                                      value="<?= $p['confidence'] * 100 ?>" max="100"></progress>
                            <span class="text-xs"><?= round($p['confidence'] * 100) ?>%</span>
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
const rankings = <?= json_encode(array_slice($rankings, 0, 10)) ?>;
const colors   = <?= json_encode($colors) ?>;

const ctx = document.getElementById('productChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: rankings.map(r => r.name.length > 18 ? r.name.substring(0,18)+'…' : r.name),
        datasets: [{
            label:           'Predicted Units',
            data:            rankings.map(r => r.predicted_qty),
            backgroundColor: rankings.map((_, i) => colors[i % colors.length] + 'CC'),
            borderColor:     rankings.map((_, i) => colors[i % colors.length]),
            borderWidth:     1.5,
            borderRadius:    6,
        }],
    },
    options: {
        responsive:          true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => `Predicted: ${ctx.parsed.y.toLocaleString()} units`,
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: {
                grid:  { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { size: 11 }, callback: v => v.toLocaleString() },
            },
        },
    },
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
