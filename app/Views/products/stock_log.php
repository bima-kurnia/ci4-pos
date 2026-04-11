<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?= base_url('products') ?>" class="btn btn-ghost btn-sm btn-circle">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <div>
        <h2 class="text-lg font-bold text-slate-800"><?= esc($title) ?></h2>
        <p class="text-xs text-slate-400">SKU: <?= esc($product['sku']) ?> • Current Stock: <strong><?= $product['stock'] ?></strong></p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                <tr><td colspan="5" class="text-center py-10 text-slate-400">No stock movements recorded.</td></tr>
                <?php else: ?>
                <?php foreach ($movements as $i => $m): ?>
                <tr class="hover">
                    <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                    <td>
                        <?php if ($m['type'] === 'in'): ?>
                            <span class="badge badge-success badge-sm gap-1">↑ In</span>
                        <?php elseif ($m['type'] === 'out'): ?>
                            <span class="badge badge-error badge-sm gap-1">↓ Out</span>
                        <?php else: ?>
                            <span class="badge badge-warning badge-sm">⟳ Adjust</span>
                        <?php endif; ?>
                    </td>
                    <td class="font-bold font-mono text-sm <?= $m['type'] === 'in' ? 'text-emerald-600' : 'text-red-500' ?>">
                        <?= $m['type'] === 'in' ? '+' : '-' ?><?= $m['quantity'] ?>
                    </td>
                    <td class="text-xs text-slate-500"><?= esc($m['reference'] ?? '—') ?></td>
                    <td class="text-xs text-slate-400"><?= date('d M Y H:i', strtotime($m['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
