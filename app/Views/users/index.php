<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><?= count($users) ?> user(s) registered.</p>
    <button onclick="addModal.showModal()" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add User
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="table table-sm w-full">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr class="hover <?= $u['id'] == session()->get('user_id') ? 'bg-sky-50/50' : '' ?>">
                <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-sky-100 text-sky-700 rounded-full w-8 text-xs font-bold">
                                <span><?= strtoupper(substr($u['name'], 0, 2)) ?></span>
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm"><?= esc($u['name']) ?></p>
                            <?php if ($u['id'] == session()->get('user_id')): ?>
                            <p class="text-xs text-sky-500">You</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="text-sm text-slate-500"><?= esc($u['email']) ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="badge badge-error badge-sm font-semibold">Admin</span>
                    <?php else: ?>
                        <span class="badge badge-info badge-sm font-semibold">Cashier</span>
                    <?php endif; ?>
                </td>
                <td class="text-xs text-slate-400"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="openEdit(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>, <?= (int)session()->get('user_id') ?>)"
                                class="btn btn-ghost btn-xs text-sky-600">Edit</button>
                        <?php if ($u['id'] != session()->get('user_id')): ?>
                        <button onclick="openDelete(<?= $u['id'] ?>, '<?= esc(addslashes($u['name'])) ?>')"
                                class="btn btn-ghost btn-xs text-red-500">Delete</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<dialog id="addModal" class="modal">
    <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg mb-4">Add User</h3>
        <form method="POST" action="<?= base_url('users/store') ?>">
            <?= csrf_field() ?>
            <div class="space-y-3">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Full Name <span class="text-red-500">*</span></span></label>
                    <input type="text" name="name" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Email <span class="text-red-500">*</span></span></label>
                    <input type="email" name="email" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Password <span class="text-red-500">*</span></span></label>
                    <input type="password" name="password" class="input input-bordered" placeholder="Min 6 characters" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Role <span class="text-red-500">*</span></span></label>
                    <select name="role" class="select select-bordered">
                        <option value="cashier">Cashier</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" onclick="addModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Edit Modal -->
<dialog id="editModal" class="modal">
    <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg mb-4">Edit User</h3>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="space-y-3">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Full Name <span class="text-red-500">*</span></span></label>
                    <input type="text" id="editName" name="name" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Email <span class="text-red-500">*</span></span></label>
                    <input type="email" id="editEmail" name="email" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Password</span></label>
                    <input type="password" name="password" class="input input-bordered" placeholder="Leave blank to keep current" />
                </div>
                <div class="form-control" id="editRoleField">
                    <label class="label"><span class="label-text font-semibold">Role <span class="text-red-500">*</span></span></label>
                    <select id="editRole" name="role" class="select select-bordered">
                        <option value="cashier">Cashier</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <!-- Shown only when editing own account -->
                <div class="form-control hidden" id="editRoleLocked">
                    <label class="label"><span class="label-text font-semibold">Role</span></label>
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl">
                        <span id="editRoleDisplay" class="font-semibold capitalize text-slate-600"></span>
                        <span class="badge badge-warning badge-sm ml-auto">🔒 Cannot change own role</span>
                    </div>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" onclick="editModal.close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Delete Modal -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete User</h3>
        <p class="py-3 text-slate-500">Delete user <strong id="deleteUserName"></strong>? This cannot be undone.</p>
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
function openEdit(u, currentId) {
    document.getElementById('editForm').action = baseUrl + '/users/update/' + u.id;
    document.getElementById('editName').value  = u.name;
    document.getElementById('editEmail').value = u.email;

    const isSelf      = (+u.id === currentId);
    const roleField   = document.getElementById('editRoleField');
    const roleLocked  = document.getElementById('editRoleLocked');
    const roleDisplay = document.getElementById('editRoleDisplay');
    const roleSelect  = document.getElementById('editRole');

    if (isSelf) {
        // Hide the select, show the locked badge
        roleField.classList.add('hidden');
        roleLocked.classList.remove('hidden');
        roleDisplay.textContent = u.role;
        roleSelect.value = u.role; // still submitted but ignored server-side
    } else {
        roleField.classList.remove('hidden');
        roleLocked.classList.add('hidden');
        roleSelect.value = u.role;
    }

    editModal.showModal();
}
function openDelete(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = baseUrl + '/users/delete/' + id;
    deleteModal.showModal();
}
</script>
<?= $this->endSection() ?>