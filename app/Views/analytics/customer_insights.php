<?= $this->extend('layouts/main') ?>
<?= $this->section('head') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="<?= base_url('analytics') ?>" class="hover:text-sky-600">Analytics</a>
        <span>›</span>
        <span class="font-semibold text-slate-800">Customer Insights</span>
    </div>
    <a href="?refresh=1" class="btn btn-sm btn-ghost gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Refresh
    </a>
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
    Cached result. <a href="?refresh=1" class="underline font-semibold">Refresh now</a>
</div>
<?php endif; ?>

<?php
$customers = $result['customers']        ?? [];
$summary   = $result['segments_summary'] ?? [];
$total     = $result['total_customers']  ?? 0;

$segmentConfig = [
    'champion'  => ['label'=>'🏆 Champions',   'color'=>'amber',   'bg'=>'bg-amber-50',   'border'=>'border-amber-200',  'badge'=>'badge-warning'],
    'loyal'     => ['label'=>'⭐ Loyal',        'color'=>'sky',     'bg'=>'bg-sky-50',     'border'=>'border-sky-200',    'badge'=>'badge-info'],
    'potential' => ['label'=>'🌱 Potential',    'color'=>'emerald', 'bg'=>'bg-emerald-50', 'border'=>'border-emerald-200','badge'=>'badge-success'],
    'at_risk'   => ['label'=>'⚠️ At Risk',      'color'=>'orange',  'bg'=>'bg-orange-50',  'border'=>'border-orange-200', 'badge'=>'badge-warning'],
    'lost'      => ['label'=>'💤 Lost',         'color'=>'slate',   'bg'=>'bg-slate-50',   'border'=>'border-slate-200',  'badge'=>'badge-ghost'],
];
$chartColors = ['#f59e0b','#0ea5e9','#10b981','#f97316','#94a3b8'];
?>

<!-- Segment summary cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    <?php foreach ($segmentConfig as $key => $cfg):
        $count = $summary[$key] ?? 0;
        $pct   = $total > 0 ? round($count / $total * 100) : 0;
    ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 mb-1"><?= $cfg['label'] ?></p>
        <p class="text-3xl font-extrabold text-slate-800"><?= $count ?></p>
        <p class="text-xs text-slate-400"><?= $pct ?>% of customers</p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    <!-- Doughnut chart -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <h3 class="font-bold text-slate-800 mb-4">Segment Distribution</h3>
        <div class="relative h-52">
            <canvas id="segmentChart"></canvas>
        </div>
        <div class="mt-4 space-y-1.5">
            <?php
            $ci = 0;
            foreach ($segmentConfig as $key => $cfg):
                $count = $summary[$key] ?? 0;
                if ($count === 0) { $ci++; continue; }
            ?>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full inline-block flex-shrink-0"
                          style="background:<?= $chartColors[$ci] ?>"></span>
                    <span class="text-xs text-slate-600"><?= $cfg['label'] ?></span>
                </div>
                <span class="text-xs font-bold text-slate-700"><?= $count ?></span>
            </div>
            <?php $ci++; endforeach; ?>
        </div>
    </div>

    <!-- RFM explanation -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <h3 class="font-bold text-slate-800 mb-1">RFM Score Explained</h3>
        <p class="text-xs text-slate-400 mb-4">Each dimension scored 1–5. Total score (3–15) determines segment.</p>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <?php foreach ([
                ['dim'=>'R','name'=>'Recency',   'desc'=>'Days since last purchase. Lower = better.', 'color'=>'sky'],
                ['dim'=>'F','name'=>'Frequency', 'desc'=>'Total number of transactions.',              'color'=>'violet'],
                ['dim'=>'M','name'=>'Monetary',  'desc'=>'Total amount spent.',                        'color'=>'emerald'],
            ] as $rfm): ?>
            <div class="bg-<?= $rfm['color'] ?>-50 rounded-xl p-3 border border-<?= $rfm['color'] ?>-100">
                <p class="text-2xl font-extrabold text-<?= $rfm['color'] ?>-600 mb-1"><?= $rfm['dim'] ?></p>
                <p class="text-xs font-bold text-slate-700"><?= $rfm['name'] ?></p>
                <p class="text-xs text-slate-500 mt-0.5"><?= $rfm['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="space-y-1.5">
            <?php foreach ([
                ['range'=>'13–15', 'seg'=>'Champion',  'desc'=>'Best customers — bought recently, often, spends most'],
                ['range'=>'10–12', 'seg'=>'Loyal',     'desc'=>'Reliable buyers with consistent spending'],
                ['range'=>'7–9',   'seg'=>'Potential', 'desc'=>'Recent but need more engagement to convert'],
                ['range'=>'4–6',   'seg'=>'At Risk',   'desc'=>'Good history but haven\'t bought in a while'],
                ['range'=>'3',     'seg'=>'Lost',      'desc'=>'Inactive — needs strong win-back campaign'],
            ] as $row): ?>
            <div class="flex items-center gap-3 text-xs">
                <span class="font-mono font-bold text-slate-500 w-10 flex-shrink-0"><?= $row['range'] ?></span>
                <span class="font-semibold text-slate-700 w-20 flex-shrink-0"><?= $row['seg'] ?></span>
                <span class="text-slate-400"><?= $row['desc'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Customer table with segment filter -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <h3 class="font-bold text-slate-800">Customer Segments (<?= $total ?> total)</h3>
        <div class="flex gap-1.5 flex-wrap" id="segFilter">
            <button class="btn btn-xs btn-primary seg-filter-btn" data-seg="">All</button>
            <?php foreach ($segmentConfig as $key => $cfg): ?>
            <?php if (($summary[$key] ?? 0) > 0): ?>
            <button class="btn btn-xs btn-ghost seg-filter-btn" data-seg="<?= $key ?>">
                <?= $cfg['label'] ?>
            </button>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table table-sm w-full" id="customerTable">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>Customer</th>
                    <th class="text-center">Segment</th>
                    <th class="text-center">R Score</th>
                    <th class="text-center">F Score</th>
                    <th class="text-center">M Score</th>
                    <th class="text-center">RFM Total</th>
                    <th class="text-right">Total Spend</th>
                    <th class="text-right">Avg Order</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-center">Last Purchase</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c):
                    $cfg = $segmentConfig[$c['segment']] ?? $segmentConfig['lost'];
                    $rfm = $c['rfm'];
                ?>
                <tr class="hover customer-row" data-seg="<?= esc($c['segment']) ?>">
                    <td class="font-semibold text-slate-800"><?= esc($c['name']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= $cfg['badge'] ?> badge-md whitespace-nowrap">
                            <?= esc($c['segment_label']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="font-bold font-mono text-sky-600"><?= $rfm['recency_score'] ?></span>
                    </td>
                    <td class="text-center">
                        <span class="font-bold font-mono text-violet-600"><?= $rfm['frequency_score'] ?></span>
                    </td>
                    <td class="text-center">
                        <span class="font-bold font-mono text-emerald-600"><?= $rfm['monetary_score'] ?></span>
                    </td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            <progress class="progress progress-warning w-10 h-1.5"
                                      value="<?= $rfm['total_rfm'] ?>" max="15"></progress>
                            <span class="text-xs font-bold"><?= $rfm['total_rfm'] ?></span>
                        </div>
                    </td>
                    <td class="text-right font-semibold">Rp <?= number_format($c['monetary'], 0, ',', '.') ?></td>
                    <td class="text-right text-sm">Rp <?= number_format($c['avg_order_value'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= (int)$c['frequency'] ?></td>
                    <td class="text-center text-xs text-slate-500"><?= (int)$c['recency_days'] ?> days ago</td>
                    <td class="text-xs text-slate-500 max-w-xs"><?= esc($c['recommendation']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Segment doughnut chart
const summary = <?= json_encode($summary) ?>;
const segKeys = ['champion','loyal','potential','at_risk','lost'];
const segLabels = { champion:'Champion', loyal:'Loyal', potential:'Potential', at_risk:'At Risk', lost:'Lost' };
const chartColors = <?= json_encode($chartColors) ?>;

const labels = segKeys.filter(k => summary[k] > 0).map(k => segLabels[k]);
const data   = segKeys.filter(k => summary[k] > 0).map(k => summary[k]);
const colors = segKeys.map((k,i) => chartColors[i]).filter((_, i) => summary[segKeys[i]] > 0);

new Chart(document.getElementById('segmentChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels,
        datasets: [{
            data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 6,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.label}: ${ctx.parsed} customers`,
                }
            }
        },
    },
});

// Segment filter
document.querySelectorAll('.seg-filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.seg-filter-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-ghost');
        });
        btn.classList.remove('btn-ghost');
        btn.classList.add('btn-primary');

        const seg = btn.dataset.seg;
        document.querySelectorAll('.customer-row').forEach(row => {
            row.style.display = (!seg || row.dataset.seg === seg) ? '' : 'none';
        });
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
