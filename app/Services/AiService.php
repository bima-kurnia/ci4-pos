<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

/**
 * AiService
 * =========
 * CI4 service class that wraps all HTTP calls to the Python FastAPI AI service.
 * Uses CI4's built-in HTTP client (CURLRequest).
 *
 * Usage:
 *   $ai = new AiService();
 *   $result = $ai->predictSales($dailySales, 30);
 */
class AiService
{
    protected string $baseUrl;
    protected int    $timeout;
    protected CURLRequest $client;

    public function __construct()
    {
        // Read from .env: ai.service_url = http://127.0.0.1:8001
        $this->baseUrl = env('ai.service_url', 'http://127.0.0.1:8001');
        $this->timeout = (int) env('ai.timeout', 15);

        $this->client = Services::curlrequest([
            'baseURI' => $this->baseUrl,
            'timeout' => $this->timeout,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // SALES FORECAST
    // ─────────────────────────────────────────────────────────────

    /**
     * Call /predict-sales
     *
     * @param  array $dailySales   [ ['date'=>'Y-m-d','revenue'=>float,'count'=>int], ... ]
     * @param  int   $forecastDays Number of days to forecast (7-90)
     * @return array               Decoded response or error array
     */
    public function predictSales(array $dailySales, int $forecastDays = 30): array
    {
        return $this->post('/predict-sales', [
            'daily_sales'   => $dailySales,
            'forecast_days' => $forecastDays,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRODUCT FORECAST
    // ─────────────────────────────────────────────────────────────

    /**
     * Call /predict-products
     *
     * @param  array $productSales  [ ['product_id'=>int, 'name'=>str, 'total_qty'=>int, 'total_revenue'=>float, 'daily_qty'=>[...]], ... ]
     * @param  int   $forecastDays
     * @return array
     */
    public function predictProducts(array $productSales, int $forecastDays = 30): array
    {
        return $this->post('/predict-products', [
            'product_sales' => $productSales,
            'forecast_days' => $forecastDays,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOMER INSIGHTS
    // ─────────────────────────────────────────────────────────────

    /**
     * Call /customer-insights
     *
     * @param  array       $customers     [ ['customer_id'=>int, 'name'=>str, 'transactions'=>[...]], ... ]
     * @param  string|null $analysisDate  Reference date Y-m-d (null = today)
     * @return array
     */
    public function customerInsights(array $customers, ?string $analysisDate = null): array
    {
        $payload = ['customers' => $customers];
        if ($analysisDate) {
            $payload['analysis_date'] = $analysisDate;
        }
        return $this->post('/customer-insights', $payload);
    }

    // ─────────────────────────────────────────────────────────────
    // HEALTH CHECK
    // ─────────────────────────────────────────────────────────────

    /**
     * Ping the AI service health endpoint.
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->client->get('/health');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            log_message('error', '[AiService::isHealthy] ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: Generic POST helper
    // ─────────────────────────────────────────────────────────────

    private function post(string $endpoint, array $payload): array
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body'    => json_encode($payload),
            ]);

            $status = $response->getStatusCode();
            $body   = json_decode($response->getBody(), true);

            if ($status !== 200) {
                log_message('error', "[AiService] {$endpoint} returned HTTP {$status}: " . $response->getBody());
                return [
                    'error'   => true,
                    'message' => $body['detail'] ?? "AI service returned HTTP {$status}",
                ];
            }

            return $body ?? [];

        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            log_message('error', "[AiService] HTTP error on {$endpoint}: " . $e->getMessage());
            return ['error' => true, 'message' => 'AI service is unreachable. Is it running?'];
        } catch (\Exception $e) {
            log_message('error', "[AiService] Unexpected error on {$endpoint}: " . $e->getMessage());
            return ['error' => true, 'message' => 'Unexpected error: ' . $e->getMessage()];
        }
    }
}