<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Today's Revenue</p>
        <p class="text-2xl font-extrabold text-sky-700">Rp <?= number_format($today_revenue, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-1"><?= date('d M Y') ?></p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Today's Sales</p>
        <p class="text-2xl font-extrabold text-emerald-600"><?= $today_count ?></p>
        <p class="text-xs text-slate-400 mt-1">Transactions</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Products</p>
        <p class="text-2xl font-extrabold text-violet-600"><?= $total_products ?></p>
        <p class="text-xs text-slate-400 mt-1">SKUs in catalog</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Customers</p>
        <p class="text-2xl font-extrabold text-amber-500"><?= $total_customers ?></p>
        <p class="text-xs text-slate-400 mt-1">Registered</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <!-- Recent Transactions -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Recent Transactions</h2>
            <a href="<?= base_url('transactions') ?>" class="text-xs text-sky-600 hover:underline font-semibold">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-sm w-full">
                <thead class="bg-slate-50 text-slate-500 text-xs">
                    <tr>
                        <th>Invoice</th>
                        <th>Cashier</th>
                        <th>Grand Total</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_transactions)): ?>
                    <tr><td colspan="5" class="text-center text-slate-400 py-8">No transactions yet</td></tr>
                    <?php else: ?>
                    <?php foreach ($recent_transactions as $tx): ?>
                    <tr class="hover">
                        <td>
                            <a href="<?= base_url('transactions/' . $tx['id']) ?>"
                               class="font-mono text-xs text-sky-600 font-semibold hover:underline">
                                <?= esc($tx['invoice_number']) ?>
                            </a>
                        </td>
                        <td class="text-xs"><?= esc($tx['cashier_name']) ?></td>
                        <td class="font-semibold text-sm">Rp <?= number_format($tx['grand_total'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($tx['status'] === 'completed'): ?>
                                <span class="badge badge-success badge-sm">Completed</span>
                            <?php elseif ($tx['status'] === 'cancelled'): ?>
                                <span class="badge badge-error badge-sm">Cancelled</span>
                            <?php else: ?>
                                <span class="badge badge-warning badge-sm">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-xs text-slate-400"><?= date('H:i', strtotime($tx['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right column -->
    <div class="flex flex-col gap-4">

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
                </svg>
                <h2 class="font-bold text-slate-800">Low Stock</h2>
            </div>
            <div class="p-3 space-y-2">
                <?php if (empty($low_stock)): ?>
                <p class="text-center text-slate-400 text-sm py-4">All products well-stocked ✓</p>
                <?php else: ?>
                <?php foreach ($low_stock as $p): ?>
                <div class="flex items-center justify-between px-2 py-1.5 bg-amber-50 rounded-xl">
                    <div>
                        <p class="text-xs font-semibold text-slate-700"><?= esc($p['name']) ?></p>
                        <p class="text-xs text-slate-400"><?= esc($p['sku'] ?? '') ?></p>
                    </div>
                    <span class="badge <?= $p['stock'] == 0 ? 'badge-error' : 'badge-warning' ?> badge-sm font-bold">
                        <?= $p['stock'] ?> left
                    </span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h2 class="font-bold text-slate-800 mb-3">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-2">
                <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-sm">New Sale</a>
                <a href="<?= base_url('products/create') ?>" class="btn btn-outline btn-sm">Add Product</a>
                <a href="<?= base_url('customers') ?>" class="btn btn-outline btn-sm">Customers</a>
                <a href="<?= base_url('transactions') ?>" class="btn btn-outline btn-sm">Reports</a>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
