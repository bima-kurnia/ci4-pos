<?php

namespace App\Controllers;

use App\Services\AiService;
use App\Models\AiPredictionModel;
use CodeIgniter\Controller;

/**
 * AnalyticsController
 * ====================
 * Admin-only controller that:
 * 1. Queries MySQL for historical data
 * 2. Formats and sends it to the Python AI service
 * 3. Caches results in ai_predictions table
 * 4. Renders analytics views
 */
class AnalyticsController extends Controller
{
    protected AiService        $ai;
    protected AiPredictionModel $cacheModel;

    // How many days of history to send to AI service
    const HISTORY_DAYS = 90;

    public function __construct()
    {
        $this->ai         = new AiService();
        $this->cacheModel = new AiPredictionModel();
        helper(['url', 'date']);
    }

    // ─────────────────────────────────────────────────────────────
    // DASHBOARD — overview of all AI features
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        $serviceOnline = $this->ai->isHealthy();

        return view('analytics/index', [
            'title'          => 'AI Analytics',
            'service_online' => $serviceOnline,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // SALES FORECAST
    // ─────────────────────────────────────────────────────────────

    public function salesForecast()
    {
        $forecastDays = (int) ($this->request->getGet('days') ?? 30);
        $forceRefresh = (bool) $this->request->getGet('refresh');
        $cacheKey     = "sales_forecast_{$forecastDays}";

        // Check cache first
        if (!$forceRefresh) {
            $cached = $this->cacheModel->getCache($cacheKey);
            if ($cached) {
                return view('analytics/sales_forecast', [
                    'title'        => 'Sales Forecast',
                    'result'       => $cached,
                    'forecast_days'=> $forecastDays,
                    'from_cache'   => true,
                ]);
            }
        }

        // ── Build payload from MySQL ──────────────────────────────
        $dailySales = $this->getDailySalesHistory(self::HISTORY_DAYS);

        if (count($dailySales) < 7) {
            return view('analytics/sales_forecast', [
                'title'         => 'Sales Forecast',
                'error'         => 'Not enough sales data. You need at least 7 days of transactions.',
                'forecast_days' => $forecastDays,
                'from_cache'    => false,
            ]);
        }

        // ── Call AI service ───────────────────────────────────────
        $result = $this->ai->predictSales($dailySales, $forecastDays);

        if (!empty($result['error'])) {
            return view('analytics/sales_forecast', [
                'title'         => 'Sales Forecast',
                'error'         => $result['message'],
                'forecast_days' => $forecastDays,
                'from_cache'    => false,
            ]);
        }

        // Cache the result
        $this->cacheModel->setCache($cacheKey, $result);

        return view('analytics/sales_forecast', [
            'title'         => 'Sales Forecast',
            'result'        => $result,
            'forecast_days' => $forecastDays,
            'from_cache'    => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRODUCT FORECAST
    // ─────────────────────────────────────────────────────────────

    public function productForecast()
    {
        $forecastDays = (int) ($this->request->getGet('days') ?? 30);
        $forceRefresh = (bool) $this->request->getGet('refresh');
        $cacheKey     = "product_forecast_{$forecastDays}";

        if (!$forceRefresh) {
            $cached = $this->cacheModel->getCache($cacheKey);
            if ($cached) {
                return view('analytics/product_forecast', [
                    'title'         => 'Product Demand Forecast',
                    'result'        => $cached,
                    'forecast_days' => $forecastDays,
                    'from_cache'    => true,
                ]);
            }
        }

        $productSales = $this->getProductSalesHistory(self::HISTORY_DAYS);

        if (empty($productSales)) {
            return view('analytics/product_forecast', [
                'title'         => 'Product Demand Forecast',
                'error'         => 'No product sales data found.',
                'forecast_days' => $forecastDays,
                'from_cache'    => false,
            ]);
        }

        $result = $this->ai->predictProducts($productSales, $forecastDays);

        if (!empty($result['error'])) {
            return view('analytics/product_forecast', [
                'title'         => 'Product Demand Forecast',
                'error'         => $result['message'],
                'forecast_days' => $forecastDays,
                'from_cache'    => false,
            ]);
        }

        $this->cacheModel->setCache($cacheKey, $result);

        return view('analytics/product_forecast', [
            'title'         => 'Product Demand Forecast',
            'result'        => $result,
            'forecast_days' => $forecastDays,
            'from_cache'    => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOMER INSIGHTS
    // ─────────────────────────────────────────────────────────────

    public function customerInsights()
    {
        $forceRefresh = (bool) $this->request->getGet('refresh');
        $cacheKey     = 'customer_insights';

        if (!$forceRefresh) {
            $cached = $this->cacheModel->getCache($cacheKey);
            if ($cached) {
                return view('analytics/customer_insights', [
                    'title'      => 'Customer Insights',
                    'result'     => $cached,
                    'from_cache' => true,
                ]);
            }
        }

        $customers = $this->getCustomerTransactionHistory();

        if (empty($customers)) {
            return view('analytics/customer_insights', [
                'title'      => 'Customer Insights',
                'error'      => 'No customer transaction data found.',
                'from_cache' => false,
            ]);
        }

        $result = $this->ai->customerInsights($customers);

        if (!empty($result['error'])) {
            return view('analytics/customer_insights', [
                'title'      => 'Customer Insights',
                'error'      => $result['message'],
                'from_cache' => false,
            ]);
        }

        $this->cacheModel->setCache($cacheKey, $result);

        return view('analytics/customer_insights', [
            'title'      => 'Customer Insights',
            'result'     => $result,
            'from_cache' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CACHE MANAGEMENT
    // ─────────────────────────────────────────────────────────────

    public function clearCache()
    {
        $this->cacheModel->clearCache();
        return redirect()->to('/analytics')->with('success', 'AI prediction cache cleared.');
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: Data preparation methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Query MySQL for daily sales aggregates over the last N days.
     *
     * Returns: [['date'=>'Y-m-d','revenue'=>float,'count'=>int], ...]
     */
    private function getDailySalesHistory(int $days): array
    {
        $db      = \Config\Database::connect();
        $cutoff  = date('Y-m-d', strtotime("-{$days} days"));

        $rows = $db->query("
            SELECT
                DATE(created_at)    AS date,
                SUM(grand_total)    AS revenue,
                COUNT(id)           AS count
            FROM transactions
            WHERE status     = 'completed'
              AND DATE(created_at) >= ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$cutoff])->getResultArray();

        return array_map(fn($r) => [
            'date'    => $r['date'],
            'revenue' => (float) $r['revenue'],
            'count'   => (int)   $r['count'],
        ], $rows);
    }

    /**
     * Query MySQL for per-product daily sales over the last N days.
     *
     * Returns the shape FastAPI expects for /predict-products.
     */
    private function getProductSalesHistory(int $days): array
    {
        $db     = \Config\Database::connect();
        $cutoff = date('Y-m-d', strtotime("-{$days} days"));

        // Aggregate totals per product
        $totals = $db->query("
            SELECT
                p.id                        AS product_id,
                p.name,
                SUM(ti.quantity)            AS total_qty,
                SUM(ti.subtotal)            AS total_revenue
            FROM transaction_items ti
            JOIN products p     ON p.id = ti.product_id
            JOIN transactions t ON t.id = ti.transaction_id
            WHERE t.status     = 'completed'
              AND DATE(t.created_at) >= ?
            GROUP BY p.id, p.name
            ORDER BY total_qty DESC
        ", [$cutoff])->getResultArray();

        if (empty($totals)) {
            return [];
        }

        // Get daily breakdown per product
        $dailyRows = $db->query("
            SELECT
                p.id                    AS product_id,
                DATE(t.created_at)      AS date,
                SUM(ti.quantity)        AS qty
            FROM transaction_items ti
            JOIN products p     ON p.id = ti.product_id
            JOIN transactions t ON t.id = ti.transaction_id
            WHERE t.status     = 'completed'
              AND DATE(t.created_at) >= ?
            GROUP BY p.id, DATE(t.created_at)
            ORDER BY p.id, date ASC
        ", [$cutoff])->getResultArray();

        // Index daily data by product_id
        $dailyByProduct = [];
        foreach ($dailyRows as $row) {
            $dailyByProduct[$row['product_id']][] = [
                'date' => $row['date'],
                'qty'  => (int) $row['qty'],
            ];
        }

        // Build final payload
        $result = [];
        foreach ($totals as $product) {
            $pid = $product['product_id'];
            $result[] = [
                'product_id'    => (int)   $pid,
                'name'          =>         $product['name'],
                'total_qty'     => (int)   $product['total_qty'],
                'total_revenue' => (float) $product['total_revenue'],
                'daily_qty'     => $dailyByProduct[$pid] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Query MySQL for customer transaction history.
     *
     * Returns the shape FastAPI expects for /customer-insights.
     * Only includes named customers (excludes walk-in, customer_id IS NOT NULL).
     */
    private function getCustomerTransactionHistory(): array
    {
        $db = \Config\Database::connect();

        // Get all named customers with at least 1 transaction
        $customers = $db->query("
            SELECT DISTINCT
                c.id   AS customer_id,
                c.name
            FROM customers c
            JOIN transactions t ON t.customer_id = c.id
            WHERE t.status = 'completed'
            ORDER BY c.name ASC
        ")->getResultArray();

        if (empty($customers)) {
            return [];
        }

        // Get all transactions for these customers
        $txRows = $db->query("
            SELECT
                customer_id,
                DATE(created_at) AS date,
                grand_total      AS amount
            FROM transactions
            WHERE status = 'completed'
              AND customer_id IS NOT NULL
            ORDER BY customer_id, created_at ASC
        ")->getResultArray();

        // Group transactions by customer_id
        $txByCustomer = [];
        foreach ($txRows as $tx) {
            $txByCustomer[$tx['customer_id']][] = [
                'date'   => $tx['date'],
                'amount' => (float) $tx['amount'],
            ];
        }

        // Build payload
        $result = [];
        foreach ($customers as $c) {
            $cid = $c['customer_id'];
            if (empty($txByCustomer[$cid])) {
                continue;
            }
            $result[] = [
                'customer_id'  => (int) $cid,
                'name'         => $c['name'],
                'transactions' => $txByCustomer[$cid],
            ];
        }

        return $result;
    }
}