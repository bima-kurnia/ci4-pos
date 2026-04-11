<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Header actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1 max-w-lg">
        <input type="text" name="search" value="<?= esc($search ?? '') ?>"
               placeholder="Search by name or SKU…"
               class="input input-bordered input-sm flex-1" />
        <select name="category" class="select select-bordered select-sm">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($category ?? '') == $cat['id'] ? 'selected' : '' ?>>
                <?= esc($cat['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm">Filter</button>
        <?php if ($search || $category): ?>
        <a href="<?= base_url('products') ?>" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (session()->get('user_role') === 'admin'): ?>
    <a href="<?= base_url('products/create') ?>" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Product
    </a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" class="text-center text-slate-400 py-12">No products found.</td></tr>
                <?php else: ?>
                <?php foreach ($products as $i => $p): ?>
                <tr class="hover">
                    <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                    <td class="font-semibold text-slate-800"><?= esc($p['name']) ?></td>
                    <td><span class="badge badge-ghost font-mono text-xs"><?= esc($p['sku']) ?></span></td>
                    <td class="text-xs text-slate-500"><?= esc($p['category_name'] ?? '—') ?></td>
                    <td class="font-semibold">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($p['stock'] == 0): ?>
                            <span class="badge badge-error badge-sm font-bold">Out of stock</span>
                        <?php elseif ($p['stock'] <= 10): ?>
                            <span class="badge badge-warning badge-sm font-bold"><?= $p['stock'] ?> (Low)</span>
                        <?php else: ?>
                            <span class="badge badge-success badge-sm"><?= $p['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= base_url('products/stock/' . $p['id']) ?>"
                               class="btn btn-ghost btn-xs" title="Stock Log">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </a>
                            <?php if (session()->get('user_role') === 'admin'): ?>
                            <a href="<?= base_url('products/edit/' . $p['id']) ?>"
                               class="btn btn-ghost btn-xs text-sky-600" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button onclick="confirmDelete('<?= base_url('products/delete/' . $p['id']) ?>', '<?= esc(addslashes($p['name'])) ?>')"
                                    class="btn btn-ghost btn-xs text-red-500" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete confirmation modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-slate-800">Confirm Delete</h3>
        <p class="py-3 text-slate-500">Are you sure you want to delete <strong id="deleteProductName"></strong>? This cannot be undone.</p>
        <div class="modal-action">
            <button onclick="deleteModal.close()" class="btn btn-ghost">Cancel</button>
            <form id="deleteForm" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function confirmDelete(url, name) {
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteForm').action = url;
    deleteModal.showModal();
}
</script>
<?= $this->endSection() ?>
