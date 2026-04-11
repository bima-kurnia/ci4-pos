<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="form-control">
            <label class="label py-0.5"><span class="label-text text-xs font-semibold text-slate-500">From Date</span></label>
            <input type="date" name="date_start"
                   value="<?= esc($filters['date_start'] ?? '') ?>"
                   class="input input-bordered input-sm" />
        </div>
        <div class="form-control">
            <label class="label py-0.5"><span class="label-text text-xs font-semibold text-slate-500">To Date</span></label>
            <input type="date" name="date_end"
                   value="<?= esc($filters['date_end'] ?? '') ?>"
                   class="input input-bordered input-sm" />
        </div>
        <div class="form-control">
            <label class="label py-0.5"><span class="label-text text-xs font-semibold text-slate-500">Status</span></label>
            <select name="status" class="select select-bordered select-sm">
                <option value="">All Status</option>
                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="pending"   <?= ($filters['status'] ?? '') === 'pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
            <a href="<?= base_url('transactions') ?>" class="btn btn-ghost btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Summary bar -->
<div class="flex items-center justify-between mb-3">
    <p class="text-sm text-slate-500 font-medium">
        <?= count($transactions) ?> transaction(s) found
    </p>
    <?php
    $totalRevenue = array_sum(array_column(
        array_filter($transactions, fn($t) => $t['status'] === 'completed'),
        'grand_total'
    ));
    ?>
    <p class="text-sm font-bold text-sky-700">
        Total: Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
    </p>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>Invoice</th>
                    <th>Date & Time</th>
                    <th>Cashier</th>
                    <th>Customer</th>
                    <th>Items Total</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-center">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr><td colspan="10" class="text-center py-12 text-slate-400">No transactions found.</td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                <tr class="hover">
                    <td>
                        <a href="<?= base_url('transactions/' . $tx['id']) ?>"
                           class="font-mono text-xs font-bold text-sky-600 hover:underline">
                            <?= esc($tx['invoice_number']) ?>
                        </a>
                    </td>
                    <td class="text-xs text-slate-500">
                        <p><?= date('d M Y', strtotime($tx['created_at'])) ?></p>
                        <p class="text-slate-400"><?= date('H:i:s', strtotime($tx['created_at'])) ?></p>
                    </td>
                    <td class="text-sm"><?= esc($tx['cashier_name']) ?></td>
                    <td class="text-sm"><?= esc($tx['customer_name'] ?? '<span class="text-slate-400">Walk-in</span>') ?></td>
                    <td class="text-sm">Rp <?= number_format($tx['total_amount'], 0, ',', '.') ?></td>
                    <td class="text-sm text-red-500">
                        <?= $tx['discount'] > 0 ? '- Rp ' . number_format($tx['discount'], 0, ',', '.') : '—' ?>
                    </td>
                    <td class="text-sm text-amber-600">Rp <?= number_format($tx['tax'], 0, ',', '.') ?></td>
                    <td class="font-bold text-slate-800">Rp <?= number_format($tx['grand_total'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($tx['status'] === 'completed'): ?>
                            <span class="badge badge-success badge-sm">Completed</span>
                        <?php elseif ($tx['status'] === 'cancelled'): ?>
                            <span class="badge badge-error badge-sm">Cancelled</span>
                        <?php else: ?>
                            <span class="badge badge-warning badge-sm">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= base_url('transactions/' . $tx['id']) ?>"
                           class="btn btn-ghost btn-xs text-sky-600">
                            View →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
