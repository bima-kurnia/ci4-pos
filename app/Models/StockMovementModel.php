<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table         = 'stock_movements';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['product_id', 'type', 'quantity', 'reference', 'created_at'];
    
    // Timestamps handled manually - table has no updated_at
    protected $useTimestamps = false;

    public function record(int $productId, string $type, int $qty, string $reference = ''): void
    {
        $this->insert([
            'product_id' => $productId,
            'type'       => $type,
            'quantity'   => $qty,
            'reference'  => $reference,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getByProduct(int $productId): array
    {
        return $this->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}