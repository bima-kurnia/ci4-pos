<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1 max-w-md">
        <input type="text" name="search" value="<?= esc($search ?? '') ?>"
               placeholder="Search by name, phone, email…"
               class="input input-bordered input-sm flex-1" />
        <button class="btn btn-primary btn-sm">Search</button>
        <?php if ($search): ?>
        <a href="<?= base_url('customers') ?>" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <?php if (session()->get('user_role') === 'admin'): ?>
    <button onclick="addModal.showModal()" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Customer
    </button>
    <?php endif; ?>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table table-sm w-full">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Registered</th>
                    <?php if (session()->get('user_role') === 'admin'): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="7" class="text-center py-12 text-slate-400">No customers found.</td></tr>
                <?php else: ?>
                <?php foreach ($customers as $i => $c): ?>
                <tr class="hover">
                    <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                    <td class="font-semibold text-slate-800"><?= esc($c['name']) ?></td>
                    <td class="text-sm"><?= esc($c['phone'] ?? '—') ?></td>
                    <td class="text-sm text-slate-500"><?= esc($c['email'] ?? '—') ?></td>
                    <td class="text-xs text-slate-400 max-w-xs truncate"><?= esc($c['address'] ?? '—') ?></td>
                    <td class="text-xs text-slate-400"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <?php if (session()->get('user_role') === 'admin'): ?>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="openEdit(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)"
                                    class="btn btn-ghost btn-xs text-sky-600">Edit</button>
                            <button onclick="openDelete(<?= $c['id'] ?>, '<?= esc(addslashes($c['name'])) ?>')"
                                    class="btn btn-ghost btn-xs text-red-500">Delete</button>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<dialog id="addModal" class="modal">
    <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg mb-4">Add Customer</h3>
        <form method="POST" action="<?= base_url('customers/store') ?>">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="form-control sm:col-span-2">
                    <label class="label"><span class="label-text font-semibold">Full Name <span class="text-red-500">*</span></span></label>
                    <input type="text" name="name" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Phone</span></label>
                    <input type="text" name="phone" class="input input-bordered" placeholder="08xx" />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Email</span></label>
                    <input type="email" name="email" class="input input-bordered" />
                </div>
                <div class="form-control sm:col-span-2">
                    <label class="label"><span class="label-text font-semibold">Address</span></label>
                    <textarea name="address" class="textarea textarea-bordered" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" onclick="addModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Customer</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Edit Modal -->
<dialog id="editModal" class="modal">
    <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg mb-4">Edit Customer</h3>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="form-control sm:col-span-2">
                    <label class="label"><span class="label-text font-semibold">Full Name <span class="text-red-500">*</span></span></label>
                    <input type="text" id="editName" name="name" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Phone</span></label>
                    <input type="text" id="editPhone" name="phone" class="input input-bordered" />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Email</span></label>
                    <input type="email" id="editEmail" name="email" class="input input-bordered" />
                </div>
                <div class="form-control sm:col-span-2">
                    <label class="label"><span class="label-text font-semibold">Address</span></label>
                    <textarea id="editAddress" name="address" class="textarea textarea-bordered" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" onclick="editModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Customer</h3>
        <p class="py-3 text-slate-500">Delete <strong id="deleteCustName"></strong>? This action cannot be undone.</p>
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
const baseUrl = '<?= base_url() ?>';
function openEdit(c) {
    document.getElementById('editForm').action = baseUrl + '/customers/update/' + c.id;
    document.getElementById('editName').value    = c.name    || '';
    document.getElementById('editPhone').value   = c.phone   || '';
    document.getElementById('editEmail').value   = c.email   || '';
    document.getElementById('editAddress').value = c.address || '';
    editModal.showModal();
}
function openDelete(id, name) {
    document.getElementById('deleteCustName').textContent = name;
    document.getElementById('deleteForm').action = baseUrl + '/customers/delete/' + id;
    deleteModal.showModal();
}
</script>
<?= $this->endSection() ?>
