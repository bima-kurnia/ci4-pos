<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="<?= base_url('products') ?>" class="btn btn-ghost btn-sm btn-circle">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="text-lg font-bold text-slate-800"><?= esc($title) ?></h2>
    </div>

    <!-- Validation errors -->
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="<?= $product ? base_url('products/update/' . $product['id']) : base_url('products/store') ?>">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="form-control sm:col-span-2">
                    <label class="label"><span class="label-text font-semibold">Product Name <span class="text-red-500">*</span></span></label>
                    <input type="text" name="name"
                           value="<?= old('name', $product['name'] ?? '') ?>"
                           placeholder="e.g. Mineral Water 600ml"
                           class="input input-bordered w-full" required />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">SKU <span class="text-red-500">*</span></span></label>
                    <input type="text" name="sku"
                           value="<?= old('sku', $product['sku'] ?? '') ?>"
                           placeholder="e.g. BEV-001"
                           class="input input-bordered w-full font-mono uppercase" required />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Category</span></label>
                    <select name="category_id" class="select select-bordered w-full">
                        <option value="">— No Category —</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= old('category_id', $product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= esc($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Price (Rp) <span class="text-red-500">*</span></span></label>
                    <input type="number" name="price"
                           value="<?= old('price', $product['price'] ?? 0) ?>"
                           min="0" step="100"
                           class="input input-bordered w-full" required />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Stock <span class="text-red-500">*</span></span></label>
                    <input type="number" name="stock"
                           value="<?= old('stock', $product['stock'] ?? 0) ?>"
                           min="0"
                           class="input input-bordered w-full" required />
                    <?php if ($product): ?>
                    <label class="label"><span class="label-text-alt text-slate-400">Changing stock will auto-create a stock movement record.</span></label>
                    <?php endif; ?>
                </div>

            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-slate-100">
                <button type="submit" class="btn btn-primary">
                    <?= $product ? 'Update Product' : 'Create Product' ?>
                </button>
                <a href="<?= base_url('products') ?>" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
