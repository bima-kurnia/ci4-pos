<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AiPredictionModel
 * ==================
 * Caches AI prediction results in the `ai_predictions` table.
 * Default TTL: 6 hours. Prevents hitting the AI service on every request.
 */
class AiPredictionModel extends Model
{
    protected $table         = 'ai_predictions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['prediction_type', 'payload', 'expires_at', 'created_at'];
    protected $useTimestamps = false;

    // Cache TTL in hours
    const CACHE_TTL_HOURS = 6;

    /**
     * Get a valid (non-expired) cached prediction.
     * Returns null if not found or expired.
     */
    public function getCache(string $type): ?array
    {
        $row = $this->where('prediction_type', $type)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$row) {
            return null;
        }

        $decoded = json_decode($row['payload'], true);
        
        if (!$decoded) {
            return null;
        }

        // Attach cache metadata
        $decoded['_cached']     = true;
        $decoded['_cached_at']  = $row['created_at'];
        $decoded['_expires_at'] = $row['expires_at'];

        return $decoded;
    }

    /**
     * Store a new prediction result in cache.
     */
    public function setCache(string $type, array $data): void
    {
        // Delete old entries for this type
        $this->where('prediction_type', $type)->delete();

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::CACHE_TTL_HOURS . ' hours'));

        $this->insert([
            'prediction_type' => $type,
            'payload'         => json_encode($data),
            'expires_at'      => $expiresAt,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Force-clear cache for a specific type (or all if null).
     */
    public function clearCache(?string $type = null): void
    {
        if ($type) {
            $this->where('prediction_type', $type)->delete();
        } else {
            $this->where('id >', 0)->delete();
        }
    }
}