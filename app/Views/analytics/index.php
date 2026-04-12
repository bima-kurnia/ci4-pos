<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Service Status Banner -->
<?php if (!$service_online): ?>
<div class="alert alert-error mb-5 shadow">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
    </svg>
    <div>
        <p class="font-bold">AI Service Offline</p>
        <p class="text-sm">The Python AI service is not reachable at <code class="bg-red-200 px-1 rounded"><?= esc(env('ai.service_url', 'http://127.0.0.1:8001')) ?></code>.
        Run <code class="bg-red-200 px-1 rounded">uvicorn main:app --port 8001 --reload</code> in your AI service directory.</p>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success mb-5 shadow py-2 text-white">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="text-sm font-semibold">AI Service is online and ready.</span>
    <a href="<?= base_url('analytics/clear-cache') ?>"
       class="btn btn-ghost btn-xs ml-auto"
       onclick="return confirm('Clear all AI prediction cache?')">
        🗑 Clear Cache
    </a>
</div>
<?php endif; ?>

<!-- Header -->
<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-slate-800">AI-Powered Analytics</h2>
    <p class="text-slate-400 text-sm mt-1">
        Machine learning insights from your sales data — powered by Python FastAPI.
    </p>
</div>

<!-- Feature Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

    <!-- Sales Forecast -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-2 bg-gradient-to-r from-sky-400 to-blue-600"></div>
        <div class="p-6">
            <div class="w-12 h-12 bg-sky-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg text-slate-800 mb-1">Sales Forecast</h3>
            <p class="text-slate-500 text-sm mb-4">
                Predict next 7 or 30 days of revenue using Linear Regression + Weekday Seasonality.
            </p>
            <div class="flex flex-wrap gap-2 mb-5">
                <span class="badge badge-outline badge-sm">Linear Regression</span>
                <span class="badge badge-outline badge-sm">Trend Detection</span>
                <span class="badge badge-outline badge-sm">Confidence Bands</span>
            </div>
            <div class="flex gap-2">
                <a href="<?= base_url('analytics/sales-forecast?days=7') ?>"
                   class="btn btn-sm btn-outline flex-1 <?= !$service_online ? 'btn-disabled' : '' ?>">
                    7 Days
                </a>
                <a href="<?= base_url('analytics/sales-forecast?days=30') ?>"
                   class="btn btn-sm btn-primary flex-1 <?= !$service_online ? 'btn-disabled' : '' ?>">
                    30 Days →
                </a>
            </div>
        </div>
    </div>

    <!-- Product Forecast -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-2 bg-gradient-to-r from-violet-400 to-purple-600"></div>
        <div class="p-6">
            <div class="w-12 h-12 bg-violet-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg text-slate-800 mb-1">Product Demand</h3>
            <p class="text-slate-500 text-sm mb-4">
                Rank products by predicted demand using Weighted Moving Average + trend amplification.
            </p>
            <div class="flex flex-wrap gap-2 mb-5">
                <span class="badge badge-outline badge-sm">Demand Ranking</span>
                <span class="badge badge-outline badge-sm">WMA</span>
                <span class="badge badge-outline badge-sm">Trend Labels</span>
            </div>
            <div class="flex gap-2">
                <a href="<?= base_url('analytics/product-forecast?days=7') ?>"
                   class="btn btn-sm btn-outline flex-1 <?= !$service_online ? 'btn-disabled' : '' ?>">
                    7 Days
                </a>
                <a href="<?= base_url('analytics/product-forecast?days=30') ?>"
                   class="btn btn-sm btn-primary flex-1 <?= !$service_online ? 'btn-disabled' : '' ?>">
                    30 Days →
                </a>
            </div>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-2 bg-gradient-to-r from-emerald-400 to-teal-600"></div>
        <div class="p-6">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg text-slate-800 mb-1">Customer Insights</h3>
            <p class="text-slate-500 text-sm mb-4">
                Segment customers by value using RFM analysis — identify champions, loyal, at-risk, and lost.
            </p>
            <div class="flex flex-wrap gap-2 mb-5">
                <span class="badge badge-outline badge-sm">RFM Scoring</span>
                <span class="badge badge-outline badge-sm">Segmentation</span>
                <span class="badge badge-outline badge-sm">Recommendations</span>
            </div>
            <a href="<?= base_url('analytics/customer-insights') ?>"
               class="btn btn-sm btn-primary w-full <?= !$service_online ? 'btn-disabled' : '' ?>">
                Analyse Customers →
            </a>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-bold text-slate-800 mb-4">How It Works</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <?php
        $steps = [
            ['icon'=>'🗄️', 'title'=>'1. Data Collection',  'desc'=>'CI4 queries your MySQL transactions, products, and customers tables.'],
            ['icon'=>'📤', 'title'=>'2. Send to AI',        'desc'=>'Formatted JSON payload is sent to the Python FastAPI service via HTTP POST.'],
            ['icon'=>'🤖', 'title'=>'3. ML Processing',     'desc'=>'FastAPI runs Linear Regression, WMA, and RFM algorithms on the data.'],
            ['icon'=>'📊', 'title'=>'4. Display Results',   'desc'=>'Predictions are cached for 6 hours and displayed as interactive charts.'],
        ];
        foreach ($steps as $step): ?>
        <div class="flex flex-col items-center text-center p-4 bg-slate-50 rounded-2xl">
            <span class="text-3xl mb-2"><?= $step['icon'] ?></span>
            <p class="font-bold text-sm text-slate-800 mb-1"><?= $step['title'] ?></p>
            <p class="text-xs text-slate-500"><?= $step['desc'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?= $this->endSection() ?>
