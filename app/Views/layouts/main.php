<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($title ?? 'POS System') ?> — SwiftPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50:  '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        };
    </script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150; }
        .sidebar-link:hover { @apply bg-white/10 text-white; }
        .sidebar-link.active { @apply bg-white text-sky-700 shadow font-semibold; }
        .sidebar-link .icon { @apply w-5 h-5 flex-shrink-0; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .stat-card { @apply bg-white rounded-2xl p-5 shadow-sm border border-slate-100; }
        .badge-role-admin   { @apply badge badge-error badge-sm font-semibold; }
        .badge-role-cashier { @apply badge badge-info badge-sm font-semibold; }
    </style>
    <?= $this->renderSection('head') ?>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="flex h-screen overflow-hidden">

    <!-- ===== SIDEBAR ===== -->
    <aside class="w-64 flex-shrink-0 bg-gradient-to-b from-sky-700 to-sky-900 text-white flex flex-col shadow-2xl z-10">

        <!-- Logo -->
        <div class="px-6 py-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow">
                    <svg class="w-5 h-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-base leading-tight">SwiftPOS</p>
                    <p class="text-xs text-sky-200">Point of Sale</p>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">

            <p class="px-4 pt-2 pb-1 text-xs font-semibold text-sky-300 uppercase tracking-wider">Main</p>

            <a href="<?= base_url('dashboard') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= uri_string() === 'dashboard' ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <a href="<?= base_url('pos') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'pos') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Point of Sale
            </a>

            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-sky-300 uppercase tracking-wider">Management</p>

            <?php if (session()->get('user_role') === 'admin'): ?>

            <a href="<?= base_url('categories') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'categories') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Categories
            </a>

            <?php endif; ?>

            <a href="<?= base_url('products') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'products') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Products
            </a>

            <a href="<?= base_url('customers') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'customers') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Customers
            </a>

            <a href="<?= base_url('transactions') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'transactions') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Transactions
            </a>

            <?php if (session()->get('user_role') === 'admin'): ?>

            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-sky-300 uppercase tracking-wider">Admin</p>

            <a href="<?= base_url('users') ?>"
               class="sidebar-link flex items-center gap-x-2 <?= str_starts_with(uri_string(), 'users') ? 'active' : 'text-sky-100' ?>">
                <svg class="icon w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
            </a>

            <?php endif; ?>
        </nav>

        <!-- User info -->
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-sky-500 text-white rounded-full w-9 text-sm font-bold">
                        <span><?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 2)) ?></span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= esc(session()->get('user_name')) ?></p>
                    <p class="text-xs text-sky-300 capitalize"><?= esc(session()->get('user_role')) ?></p>
                </div>
                <a href="<?= base_url('logout') ?>" class="text-sky-300 hover:text-white transition" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="bg-white border-b border-slate-100 px-6 py-3.5 flex items-center justify-between shadow-sm flex-shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800"><?= esc($title ?? 'Dashboard') ?></h1>
                <p class="text-xs text-slate-400"><?= date('l, d F Y') ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?php if (session()->get('user_role') !== 'admin'): ?>
                <span class="badge badge-info badge-sm">Cashier Mode</span>
                <?php endif; ?>
                <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4"/>
                    </svg>
                    New Sale
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-6 pt-4 space-y-2">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success shadow-sm mb-0 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error shadow-sm mb-0 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-error shadow-sm mb-0 flex justify-between items-start text-white">
                    <div class="flex items-start">
                        <ul class="list-disc list-inside text-sm">
                            <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                                <li><?= esc($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button type="button" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto px-6 py-4">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<?= $this->renderSection('scripts') ?>
</body>
</html>
