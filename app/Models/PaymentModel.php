<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'transaction_id', 'method', 'amount_paid',
        'change_amount', 'payment_status', 'reference',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    public function getByTransaction(int $transactionId): ?array
    {
        return $this->where('transaction_id', $transactionId)->first();
    }
}