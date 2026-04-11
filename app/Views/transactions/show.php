<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?= base_url('transactions') ?>" class="btn btn-ghost btn-sm btn-circle">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <div class="flex-1">
        <h2 class="text-lg font-bold text-slate-800 font-mono"><?= esc($transaction['invoice_number']) ?></h2>
        <p class="text-xs text-slate-400"><?= date('l, d F Y — H:i:s', strtotime($transaction['created_at'])) ?></p>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-outline btn-sm gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        <?php if (session()->get('user_role') === 'admin' && $transaction['status'] !== 'cancelled'): ?>
        <form method="POST" action="<?= base_url('transactions/cancel/' . $transaction['id']) ?>"
              onsubmit="return confirm('Cancel this transaction?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-error btn-sm">Cancel Transaction</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Receipt / Items -->
    <div class="lg:col-span-2 space-y-4">

        <!-- Items table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Items Purchased</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $item): ?>
                        <tr>
                            <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                            <td class="font-semibold text-slate-800"><?= esc($item['product_name']) ?></td>
                            <td><span class="badge badge-ghost font-mono text-xs"><?= esc($item['sku']) ?></span></td>
                            <td class="text-right">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td class="text-center font-bold"><?= $item['quantity'] ?></td>
                            <td class="text-right font-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                        <tr>
                            <td colspan="5" class="text-right font-semibold text-slate-500 text-sm">Items Total</td>
                            <td class="text-right font-bold text-slate-800">Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                        </tr>
                        <?php if ($transaction['discount'] > 0): ?>
                        <tr>
                            <td colspan="5" class="text-right font-semibold text-red-500 text-sm">Discount</td>
                            <td class="text-right font-bold text-red-500">- Rp <?= number_format($transaction['discount'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="5" class="text-right font-semibold text-amber-600 text-sm">Tax (11%)</td>
                            <td class="text-right font-bold text-amber-600">Rp <?= number_format($transaction['tax'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="border-t border-slate-300">
                            <td colspan="5" class="text-right font-extrabold text-slate-800">GRAND TOTAL</td>
                            <td class="text-right font-extrabold text-sky-700 text-lg">Rp <?= number_format($transaction['grand_total'], 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Info cards -->
    <div class="space-y-4">

        <!-- Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Transaction Status</h3>
            <?php if ($transaction['status'] === 'completed'): ?>
                <div class="flex items-center gap-2 p-3 bg-emerald-50 rounded-xl">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-bold text-emerald-700">Completed</span>
                </div>
            <?php elseif ($transaction['status'] === 'cancelled'): ?>
                <div class="flex items-center gap-2 p-3 bg-red-50 rounded-xl">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-bold text-red-700">Cancelled</span>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 p-3 bg-amber-50 rounded-xl">
                    <span class="font-bold text-amber-700">Pending</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment info -->
        <?php if ($payment): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Payment</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Method</span>
                    <span class="font-semibold capitalize badge badge-outline"><?= esc($payment['method']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Amount Paid</span>
                    <span class="font-bold text-slate-800">Rp <?= number_format($payment['amount_paid'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between border-t border-slate-100 pt-2">
                    <span class="text-slate-500">Change</span>
                    <span class="font-bold text-emerald-600">Rp <?= number_format($payment['change_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status</span>
                    <span class="badge badge-success badge-sm"><?= esc($payment['payment_status']) ?></span>
                </div>
                <?php if ($payment['reference']): ?>
                <div class="flex justify-between">
                    <span class="text-slate-500">Reference</span>
                    <span class="font-mono text-xs"><?= esc($payment['reference']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Transaction info -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Details</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Cashier</span>
                    <span class="font-semibold"><?= esc($transaction['cashier_name']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Customer</span>
                    <span class="font-semibold"><?= esc($transaction['customer_name'] ?? 'Walk-in') ?></span>
                </div>
                <?php if ($transaction['customer_phone']): ?>
                <div class="flex justify-between">
                    <span class="text-slate-500">Phone</span>
                    <span><?= esc($transaction['customer_phone']) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between border-t border-slate-100 pt-2">
                    <span class="text-slate-500">Created</span>
                    <span class="text-xs"><?= date('d M Y H:i', strtotime($transaction['created_at'])) ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('head') ?>
<style>
@media print {
    aside, header, .btn, form { display: none !important; }
    .shadow-sm { box-shadow: none !important; }
    body { background: white !important; }
}
</style>
<?= $this->endSection() ?>
