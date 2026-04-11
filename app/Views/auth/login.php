<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — SwiftPOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } } };
    </script>
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-900 via-sky-800 to-indigo-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-2xl mb-4">
                <svg class="w-8 h-8 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white">SwiftPOS</h1>
            <p class="text-sky-300 text-sm mt-1">Point of Sale System</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-1">Welcome back</h2>
            <p class="text-slate-400 text-sm mb-6">Sign in to your account to continue.</p>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error mb-4 py-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z" />
                </svg>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success mb-4 py-2 text-sm">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-control mb-4">
                    <label class="label pb-1"><span class="label-text font-semibold text-slate-700">Email</span></label>
                    <input type="email" name="email" placeholder="you@company.com"
                           value="<?= old('email') ?>"
                           class="input input-bordered w-full focus:input-primary" required />
                </div>

                <div class="form-control mb-6">
                    <label class="label pb-1"><span class="label-text font-semibold text-slate-700">Password</span></label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="input input-bordered w-full focus:input-primary" required />
                </div>

                <button type="submit" class="btn btn-primary w-full text-base font-bold">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-sky-400 text-xs mt-6">© <?= date('Y') ?> SwiftPOS. All rights reserved.</p>
    </div>
</body>
</html>
