<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionItemModel extends Model
{
    protected $table         = 'transaction_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['transaction_id', 'product_id', 'price', 'quantity', 'subtotal'];
    protected $useTimestamps = false;

    /**
     * Get items for a transaction, with product name
     */
    public function getByTransaction(int $transactionId): array
    {
        return $this->select('transaction_items.*, products.name AS product_name, products.sku')
            ->join('products', 'products.id = transaction_items.product_id', 'left')
            ->where('transaction_items.transaction_id', $transactionId)
            ->findAll();
    }
}