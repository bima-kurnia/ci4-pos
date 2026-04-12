<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Point of Sale — SwiftPOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] } } }
        };
    </script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        .product-card {
            transition: transform 0.12s ease, box-shadow 0.12s ease;
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        }
        .product-card:active { transform: scale(0.97); }
        .product-card.out-of-stock { opacity: 0.45; cursor: not-allowed; }

        .cart-item-enter {
            animation: slideIn 0.2s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .qty-badge {
            min-width: 20px;
            height: 20px;
            font-size: 10px;
            font-weight: 700;
        }
    </style>
</head>
<body class="bg-slate-100 h-screen overflow-hidden">

<!-- Top Nav -->
<div class="bg-sky-700 text-white px-5 py-3 flex items-center justify-between shadow-md h-14 flex-shrink-0">
    <div class="flex items-center gap-4">
        <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2 text-sky-200 hover:text-white transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Dashboard
        </a>
        <span class="text-sky-500">|</span>
        <span class="font-bold text-base">Point of Sale</span>
    </div>
    <div class="flex items-center gap-3 text-sm">
        <span class="text-sky-200" id="currentTime"></span>
        <span class="text-sky-300">•</span>
        <span class="text-sky-100"><?= esc(session()->get('user_name')) ?></span>
        <span class="badge badge-sm <?= session()->get('user_role') === 'admin' ? 'badge-error' : 'badge-info' ?> capitalize">
            <?= esc(session()->get('user_role')) ?>
        </span>
    </div>
</div>

<!-- Main POS Layout -->
<div class="flex h-[calc(100vh-3.5rem)] overflow-hidden">

    <!-- ===== LEFT: Product Panel ===== -->
    <div class="flex-1 flex flex-col overflow-hidden bg-slate-100">

        <!-- Search + Category filter -->
        <div class="bg-white border-b border-slate-200 px-4 py-3 flex gap-3 items-center flex-shrink-0">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="productSearch"
                       placeholder="Search product or SKU…"
                       class="input input-bordered input-sm pl-9 w-full" />
            </div>
            <div class="flex gap-1.5 overflow-x-auto pb-0.5" id="categoryFilters">
                <button class="btn btn-xs btn-primary category-btn" data-cat="">All</button>
                <?php foreach ($products as $p):
                    if (empty($p['category_name'])) continue; ?>
                <?php endforeach; ?>
                <?php
                $seen = [];
                foreach ($products as $p) {
                    if (!empty($p['category_name']) && !in_array($p['category_name'], $seen)) {
                        $seen[] = $p['category_name'];
                        echo '<button class="btn btn-xs btn-ghost category-btn whitespace-nowrap" data-cat="' . esc($p['category_name']) . '">' . esc($p['category_name']) . '</button>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3" id="productGrid">
                <?php foreach ($products as $p): ?>
                <div class="product-card bg-white rounded-2xl shadow-sm border border-slate-100 p-3 select-none <?= $p['stock'] <= 0 ? 'out-of-stock' : '' ?>"
                     data-id="<?= $p['id'] ?>"
                     data-name="<?= esc($p['name']) ?>"
                     data-price="<?= $p['price'] ?>"
                     data-stock="<?= $p['stock'] ?>"
                     data-sku="<?= esc($p['sku']) ?>"
                     data-category="<?= esc($p['category_name'] ?? '') ?>"
                     onclick="addToCart(this)">
                    <!-- Product image -->
                    <div class="w-full aspect-square rounded-xl overflow-hidden mb-2 relative bg-slate-100">
                        <?php if (!empty($p['image'])): ?>
                        <img src="<?= esc($p['image']) ?>"
                             alt="<?= esc($p['name']) ?>"
                             class="w-full h-full object-cover"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                        <div class="w-full h-full items-center justify-center bg-gradient-to-br from-sky-50 to-indigo-50 absolute inset-0 hidden">
                            <svg class="w-9 h-9 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-sky-50 to-indigo-50">
                            <svg class="w-9 h-9 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['stock'] <= 0): ?>
                        <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                            <span class="text-xs font-bold text-red-500">OUT</span>
                        </div>
                        <?php elseif ($p['stock'] <= 10): ?>
                        <span class="absolute top-1 right-1 badge badge-warning badge-xs font-bold"><?= $p['stock'] ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs font-semibold text-slate-800 leading-tight truncate"><?= esc($p['name']) ?></p>
                    <p class="text-xs text-slate-400 font-mono mt-0.5"><?= esc($p['sku']) ?></p>
                    <p class="text-sm font-extrabold text-sky-700 mt-1">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <p id="noResults" class="text-center text-slate-400 py-16 hidden">No products found.</p>
        </div>
    </div>

    <!-- ===== RIGHT: Cart Panel ===== -->
    <div class="w-80 xl:w-96 flex-shrink-0 bg-white border-l border-slate-200 flex flex-col shadow-xl">

        <!-- Cart header -->
        <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="font-bold text-slate-800">Cart</span>
                <span class="badge badge-primary badge-sm font-bold" id="cartCount">0</span>
            </div>
            <button onclick="clearCart()" class="btn btn-ghost btn-xs text-red-400 hover:text-red-600">
                Clear All
            </button>
        </div>

        <!-- Customer selector -->
        <div class="px-4 py-2.5 border-b border-slate-100 flex-shrink-0">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Customer</label>
            <select id="customerSelect" class="select select-bordered select-sm w-full">
                <option value="">— Walk-in Customer —</option>
                <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['phone'] ? ' — ' . esc($c['phone']) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Cart items list -->
        <div class="flex-1 overflow-y-auto px-3 py-2" id="cartList">
            <!-- Empty state: always in DOM, toggled with display -->
            <div class="text-center py-12 text-slate-300" id="emptyCart">
                <svg class="w-14 h-14 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-sm font-medium">Cart is empty</p>
                <p class="text-xs">Tap a product to add it</p>
            </div>
            <!-- Cart rows injected here — separate from emptyCart -->
            <div id="cartItems"></div>
        </div>

        <!-- Discount selector -->
        <div class="px-4 py-2.5 border-t border-slate-100 flex-shrink-0">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Discount</label>
            <select id="discountSelect" class="select select-bordered select-sm w-full" onchange="recalculate()">
                <?php foreach ($discounts as $d): ?>
                <option value="<?= $d['id'] ?>" data-type="<?= $d['type'] ?>" data-value="<?= $d['value'] ?>">
                    <?= esc($d['name']) ?>
                    <?php if ($d['value'] > 0): ?>
                        (<?= $d['type'] === 'percentage' ? $d['value'] . '%' : 'Rp ' . number_format($d['value'], 0, ',', '.') ?>)
                    <?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Totals -->
        <div class="px-4 py-3 border-t border-slate-100 bg-slate-50 flex-shrink-0 space-y-1.5">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="font-semibold" id="displaySubtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-sm" id="discountRow">
                <span class="text-slate-500">Discount</span>
                <span class="font-semibold text-red-500" id="displayDiscount">— Rp 0</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Tax (11%)</span>
                <span class="font-semibold text-amber-600" id="displayTax">Rp 0</span>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2 mt-1">
                <span class="font-extrabold text-slate-800">GRAND TOTAL</span>
                <span class="font-extrabold text-sky-700 text-lg font-mono" id="displayGrandTotal">Rp 0</span>
            </div>
        </div>

        <!-- Checkout button -->
        <div class="px-4 py-3 flex-shrink-0">
            <button id="checkoutBtn"
                    onclick="openPaymentModal()"
                    class="btn btn-primary w-full text-base font-bold gap-2"
                    disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Process Payment
            </button>
        </div>
    </div>
</div>

<!-- ===== PAYMENT MODAL ===== -->
<dialog id="paymentModal" class="modal">
    <div class="modal-box max-w-md">
        <h3 class="font-bold text-xl text-slate-800 mb-1">Process Payment</h3>
        <p class="text-slate-400 text-sm mb-5">Complete the transaction below.</p>

        <!-- Summary -->
        <div class="bg-slate-50 rounded-2xl p-4 mb-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Items Total</span>
                <span class="font-semibold" id="paySubtotal">—</span>
            </div>
            <div class="flex justify-between" id="payDiscountRow">
                <span class="text-slate-500">Discount</span>
                <span class="font-semibold text-red-500" id="payDiscount">—</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tax (11%)</span>
                <span class="font-semibold text-amber-600" id="payTax">—</span>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2 font-extrabold text-sky-700">
                <span>Grand Total</span>
                <span class="text-lg font-mono" id="payGrandTotal">—</span>
            </div>
        </div>

        <!-- Payment method -->
        <div class="form-control mb-4">
            <label class="label"><span class="label-text font-semibold">Payment Method</span></label>
            <div class="grid grid-cols-4 gap-2" id="methodBtns">
                <?php foreach (['cash' => '💵 Cash', 'card' => '💳 Card', 'transfer' => '🏦 Transfer', 'ewallet' => '📱 E-Wallet'] as $val => $label): ?>
                <button type="button"
                        onclick="selectMethod('<?= $val ?>')"
                        class="method-btn btn btn-sm btn-outline <?= $val === 'cash' ? 'btn-primary' : '' ?>"
                        data-method="<?= $val ?>">
                    <?= $label ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Amount Paid -->
        <div class="form-control mb-3">
            <label class="label">
                <span class="label-text font-semibold">Amount Paid (Rp)</span>
            </label>
            <input type="number" id="amountPaidInput"
                   class="input input-bordered input-lg font-mono text-right text-xl font-bold"
                   placeholder="0" min="0" step="1000"
                   oninput="calcChange()" />
            <!-- Quick amount buttons -->
            <div class="flex flex-wrap gap-1.5 mt-2" id="quickAmounts"></div>
        </div>

        <!-- Change -->
        <div class="bg-emerald-50 rounded-xl px-4 py-3 flex justify-between items-center mb-5">
            <span class="text-sm font-semibold text-emerald-700">Change</span>
            <span class="font-extrabold text-emerald-700 text-xl font-mono" id="changeDisplay">Rp 0</span>
        </div>

        <!-- Error alert -->
        <div id="checkoutError" class="alert alert-error text-sm mb-4 hidden">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
            </svg>
            <span id="checkoutErrorMsg"></span>
        </div>

        <div class="modal-action gap-3">
            <button onclick="paymentModal.close()" class="btn btn-ghost flex-1">Cancel</button>
            <button id="confirmPayBtn" onclick="confirmCheckout()" class="btn btn-primary flex-1 font-bold text-base">
                Confirm Payment
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- ===== SUCCESS MODAL ===== -->
<dialog id="successModal" class="modal">
    <div class="modal-box max-w-sm text-center">
        <div class="flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-full mx-auto mb-4">
            <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="font-extrabold text-2xl text-slate-800 mb-1">Payment Successful!</h3>
        <p class="text-slate-400 text-sm mb-2">Transaction completed.</p>
        <p class="font-mono font-bold text-sky-600 mb-1" id="successInvoice"></p>
        <div class="bg-slate-50 rounded-2xl p-4 my-4 text-left space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Grand Total</span>
                <span class="font-bold" id="successGrandTotal"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Amount Paid</span>
                <span class="font-bold" id="successAmountPaid"></span>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2">
                <span class="text-emerald-600 font-bold">Change</span>
                <span class="font-extrabold text-emerald-600 text-lg" id="successChange"></span>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="newTransaction()" class="btn btn-primary flex-1 font-bold">New Sale</button>
            <a id="successDetailLink" href="#" class="btn btn-outline flex-1">View Receipt</a>
        </div>
    </div>
</dialog>

<script>
// ===================================================
// STATE
// ===================================================
const TAX_RATE  = <?= \App\Controllers\PosController::TAX_RATE ?>;
const BASE_URL  = '<?= base_url() ?>';
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

let cart         = {};   // { productId: { id, name, price, stock, qty } }
let grandTotal   = 0;
let subtotal     = 0;
let discountAmt  = 0;
let taxAmt       = 0;
let selectedMethod = 'cash';

// ===================================================
// CLOCK
// ===================================================
function updateClock() {
    const now = new Date();
    document.getElementById('currentTime').textContent =
        now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
setInterval(updateClock, 1000);
updateClock();

// ===================================================
// PRODUCT SEARCH & FILTER
// ===================================================
const productCards = document.querySelectorAll('.product-card');
const searchInput  = document.getElementById('productSearch');
const noResults    = document.getElementById('noResults');

let activeCategory = '';

searchInput.addEventListener('input', filterProducts);

document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        activeCategory = btn.dataset.cat;
        document.querySelectorAll('.category-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-ghost');
        });
        btn.classList.remove('btn-ghost');
        btn.classList.add('btn-primary');
        filterProducts();
    });
});

function filterProducts() {
    const q = searchInput.value.toLowerCase().trim();
    let visible = 0;

    productCards.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const sku  = card.dataset.sku.toLowerCase();
        const cat  = card.dataset.category;

        const matchSearch   = !q || name.includes(q) || sku.includes(q);
        const matchCategory = !activeCategory || cat === activeCategory;

        if (matchSearch && matchCategory) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    noResults.classList.toggle('hidden', visible > 0);
}

// ===================================================
// CART LOGIC
// ===================================================
function addToCart(card) {
    const id    = card.dataset.id;
    const stock = parseInt(card.dataset.stock);
    if (stock <= 0) return;

    if (cart[id]) {
        if (cart[id].qty >= stock) {
            showToast(`Max stock for "${cart[id].name}" is ${stock}.`, 'warning');
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = {
            id:    parseInt(id),
            name:  card.dataset.name,
            price: parseFloat(card.dataset.price),
            stock: stock,
            qty:   1,
        };
    }
    renderCart();
}

function updateQty(id, delta) {
    if (!cart[id]) return;
    const newQty = cart[id].qty + delta;
    if (newQty <= 0) {
        removeFromCart(id);
        return;
    }
    if (newQty > cart[id].stock) {
        showToast(`Only ${cart[id].stock} in stock!`, 'warning');
        return;
    }
    cart[id].qty = newQty;
    renderCart();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const empty     = document.getElementById('emptyCart');
    const keys      = Object.keys(cart);

    document.getElementById('cartCount').textContent = keys.reduce((s, k) => s + cart[k].qty, 0);

    if (keys.length === 0) {
        cartItems.innerHTML = '';
        empty.style.display = '';
        document.getElementById('checkoutBtn').disabled = true;
        recalculate();
        return;
    }

    empty.style.display = 'none';

    let html = '';
    keys.forEach(id => {
        const item    = cart[id];
        const rowTotal = item.price * item.qty;
        html += `
        <div class="flex items-center gap-2.5 py-2.5 border-b border-slate-100 cart-item-enter" data-id="${id}">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 leading-tight truncate">${escHtml(item.name)}</p>
                <p class="text-xs text-slate-400">Rp ${fmtNum(item.price)}</p>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="updateQty('${id}', -1)"
                        class="btn btn-ghost btn-xs btn-circle text-slate-500 hover:btn-error">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                    </svg>
                </button>
                <span class="w-7 text-center font-bold text-sm">${item.qty}</span>
                <button onclick="updateQty('${id}', 1)"
                        class="btn btn-ghost btn-xs btn-circle text-slate-500 hover:btn-success">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
            <div class="w-20 text-right">
                <p class="text-xs font-bold text-sky-700">Rp ${fmtNum(rowTotal)}</p>
            </div>
            <button onclick="removeFromCart('${id}')"
                    class="btn btn-ghost btn-xs btn-circle text-red-400 hover:text-red-600 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>`;
    });

    cartItems.innerHTML = html;
    document.getElementById('checkoutBtn').disabled = false;
    recalculate();
}

// ===================================================
// TOTALS CALCULATION (client-side)
// ===================================================
function recalculate() {
    subtotal = Object.values(cart).reduce((s, item) => s + item.price * item.qty, 0);

    const discSel  = document.getElementById('discountSelect');
    const opt      = discSel.options[discSel.selectedIndex];
    const dtype    = opt?.dataset.type;
    const dvalue   = parseFloat(opt?.dataset.value || 0);

    if (dtype === 'percentage') {
        discountAmt = Math.round(subtotal * (dvalue / 100));
    } else if (dtype === 'fixed') {
        discountAmt = Math.min(dvalue, subtotal);
    } else {
        discountAmt = 0;
    }

    const afterDisc = subtotal - discountAmt;
    taxAmt          = Math.round(afterDisc * TAX_RATE);
    grandTotal      = afterDisc + taxAmt;

    document.getElementById('displaySubtotal').textContent   = 'Rp ' + fmtNum(subtotal);
    document.getElementById('displayDiscount').textContent   = '— Rp ' + fmtNum(discountAmt);
    document.getElementById('displayTax').textContent        = 'Rp ' + fmtNum(taxAmt);
    document.getElementById('displayGrandTotal').textContent = 'Rp ' + fmtNum(grandTotal);

    document.getElementById('discountRow').style.display = discountAmt > 0 ? '' : 'none';
}

// ===================================================
// PAYMENT MODAL
// ===================================================
function openPaymentModal() {
    if (Object.keys(cart).length === 0) return;
    recalculate();

    document.getElementById('paySubtotal').textContent  = 'Rp ' + fmtNum(subtotal);
    document.getElementById('payDiscount').textContent  = '— Rp ' + fmtNum(discountAmt);
    document.getElementById('payTax').textContent       = 'Rp ' + fmtNum(taxAmt);
    document.getElementById('payGrandTotal').textContent= 'Rp ' + fmtNum(grandTotal);
    document.getElementById('payDiscountRow').style.display = discountAmt > 0 ? '' : 'none';

    // Build quick amount buttons
    buildQuickAmounts();

    // Reset
    document.getElementById('amountPaidInput').value = '';
    document.getElementById('changeDisplay').textContent = 'Rp 0';
    document.getElementById('checkoutError').classList.add('hidden');

    paymentModal.showModal();
}

function buildQuickAmounts() {
    const container = document.getElementById('quickAmounts');
    const amounts = [grandTotal, 
        Math.ceil(grandTotal / 10000) * 10000,
        Math.ceil(grandTotal / 50000) * 50000,
        Math.ceil(grandTotal / 100000) * 100000,
    ];
    const unique = [...new Set(amounts)].slice(0, 4);
    container.innerHTML = unique.map(a =>
        `<button type="button" onclick="setAmount(${a})"
                 class="btn btn-outline btn-xs font-mono">
            Rp ${fmtNum(a)}
        </button>`
    ).join('');
}

function setAmount(val) {
    document.getElementById('amountPaidInput').value = val;
    calcChange();
}

function calcChange() {
    const paid   = parseFloat(document.getElementById('amountPaidInput').value) || 0;
    const change = paid - grandTotal;
    const el     = document.getElementById('changeDisplay');
    if (change >= 0) {
        el.textContent = 'Rp ' + fmtNum(change);
        el.className   = 'font-extrabold text-emerald-700 text-xl font-mono';
    } else {
        el.textContent = '- Rp ' + fmtNum(Math.abs(change));
        el.className   = 'font-extrabold text-red-500 text-xl font-mono';
    }
}

function selectMethod(method) {
    selectedMethod = method;
    document.querySelectorAll('.method-btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });
    document.querySelector(`[data-method="${method}"]`).classList.add('btn-primary');
    document.querySelector(`[data-method="${method}"]`).classList.remove('btn-outline');
}

// ===================================================
// CHECKOUT (AJAX to PosController::checkout)
// ===================================================
async function confirmCheckout() {
    const amountPaid = parseFloat(document.getElementById('amountPaidInput').value) || 0;

    if (amountPaid < grandTotal) {
        showError('Amount paid is less than the grand total.');
        return;
    }

    const discSel    = document.getElementById('discountSelect');
    const customerId = document.getElementById('customerSelect').value;
    const discountId = discSel.value;

    const payload = {
        cart: Object.values(cart).map(item => ({
            product_id: item.id,
            quantity:   item.qty,
        })),
        customer_id:    customerId || null,
        discount_id:    discountId || null,
        payment_method: selectedMethod,
        amount_paid:    amountPaid,
    };

    // Disable confirm button during request
    const btn = document.getElementById('confirmPayBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Processing…';

    try {
        const res  = await fetch(BASE_URL + '/pos/checkout', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [CSRF_NAME]: CSRF_HASH,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            // Show success modal
            paymentModal.close();
            document.getElementById('successInvoice').textContent    = data.invoice_number;
            document.getElementById('successGrandTotal').textContent = 'Rp ' + fmtNum(data.grand_total);
            document.getElementById('successAmountPaid').textContent = 'Rp ' + fmtNum(data.amount_paid);
            document.getElementById('successChange').textContent     = 'Rp ' + fmtNum(data.change_amount);
            document.getElementById('successDetailLink').href        = data.redirect;
            successModal.showModal();
        } else {
            showError(data.message || 'Checkout failed.');
        }
    } catch (err) {
        showError('Network error. Please try again.');
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Confirm Payment';
    }
}

function showError(msg) {
    const el = document.getElementById('checkoutError');
    document.getElementById('checkoutErrorMsg').textContent = msg;
    el.classList.remove('hidden');
}

function newTransaction() {
    successModal.close();
    clearCart();
    document.getElementById('customerSelect').value = '';
    document.getElementById('discountSelect').selectedIndex = 0;
    recalculate();
    // Refresh product stock display
    location.reload();
}

// ===================================================
// HELPERS
// ===================================================
function fmtNum(n) {
    return Math.round(n).toLocaleString('id-ID');
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = 'info') {
    const t  = document.createElement('div');
    t.className = `alert alert-${type} shadow-lg fixed top-4 right-4 z-50 max-w-xs text-sm animate-bounce`;
    t.innerHTML = `<span>${escHtml(msg)}</span>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

// Init
recalculate();
</script>
</body>
</html>
