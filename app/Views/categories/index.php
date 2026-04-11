<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><?= count($categories) ?> categories found.</p>
    <button onclick="addModal.showModal()" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Category
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="table table-sm w-full">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Created</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
            <tr><td colspan="4" class="text-center py-10 text-slate-400">No categories yet.</td></tr>
            <?php else: ?>
            <?php foreach ($categories as $i => $cat): ?>
            <tr class="hover">
                <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                <td class="font-semibold text-slate-800"><?= esc($cat['name']) ?></td>
                <td class="text-xs text-slate-400"><?= date('d M Y', strtotime($cat['created_at'])) ?></td>
                <td>
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="openEdit(<?= $cat['id'] ?>, '<?= esc(addslashes($cat['name'])) ?>')"
                                class="btn btn-ghost btn-xs text-sky-600">Edit</button>
                        <button onclick="openDelete(<?= $cat['id'] ?>, '<?= esc(addslashes($cat['name'])) ?>')"
                                class="btn btn-ghost btn-xs text-red-500">Delete</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<dialog id="addModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Add Category</h3>
        <form method="POST" action="<?= base_url('categories/store') ?>">
            <?= csrf_field() ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-semibold">Category Name</span></label>
                <input type="text" name="name" placeholder="e.g. Beverages" class="input input-bordered" required />
            </div>
            <div class="modal-action">
                <button type="button" onclick="addModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Edit Modal -->
<dialog id="editModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Edit Category</h3>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-semibold">Category Name</span></label>
                <input type="text" id="editName" name="name" class="input input-bordered" required />
            </div>
            <div class="modal-action">
                <button type="button" onclick="editModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Category</h3>
        <p class="py-3 text-slate-500">Delete <strong id="deleteName"></strong>? This cannot be undone.</p>
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
function openEdit(id, name) {
    document.getElementById('editForm').action = baseUrl + '/categories/update/' + id;
    document.getElementById('editName').value = name;
    editModal.showModal();
}
function openDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = baseUrl + '/categories/delete/' + id;
    deleteModal.showModal();
}
</script>
<?= $this->endSection() ?>
