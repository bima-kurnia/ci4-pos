<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'invoice_number', 'user_id', 'customer_id',
        'total_amount', 'discount', 'tax', 'grand_total', 'status',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Auto-generate a unique invoice number: INV-YYYYMMDD-XXXXX
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $last   = $this->like('invoice_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last['invoice_number']);
            $seq   = (int) end($parts) + 1;
        }
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get transaction list with user and customer names
     */
    public function getWithDetails(array $filters = []): array
    {
        $builder = $this->select('transactions.*, users.name AS cashier_name, customers.name AS customer_name')
            ->join('users', 'users.id = transactions.user_id', 'left')
            ->join('customers', 'customers.id = transactions.customer_id', 'left')
            ->orderBy('transactions.created_at', 'DESC');

        if (!empty($filters['date_start'])) {
            $builder->where('DATE(transactions.created_at) >=', $filters['date_start']);
        }
        if (!empty($filters['date_end'])) {
            $builder->where('DATE(transactions.created_at) <=', $filters['date_end']);
        }
        if (!empty($filters['status'])) {
            $builder->where('transactions.status', $filters['status']);
        }

        return $builder->findAll();
    }

    /**
     * Get one transaction with full details
     */
    public function getDetail(int $id): ?array
    {
        return $this->select('transactions.*, users.name AS cashier_name, customers.name AS customer_name, customers.phone AS customer_phone')
            ->join('users', 'users.id = transactions.user_id', 'left')
            ->join('customers', 'customers.id = transactions.customer_id', 'left')
            ->find($id);
    }

    /**
     * Daily revenue summary
     */
    public function getDailySummary(): array
    {
        return $this->select('DATE(created_at) AS date, SUM(grand_total) AS total, COUNT(id) AS count')
            ->where('status', 'completed')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'DESC')
            ->limit(7)
            ->findAll();
    }

    /**
     * Today's total revenue
     */
    public function getTodayRevenue(): float
    {
        $row = $this->select('SUM(grand_total) AS revenue')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'completed')
            ->first();
        return (float) ($row['revenue'] ?? 0);
    }

    /**
     * Count today's transactions
     */
    public function getTodayCount(): int
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'completed')
            ->countAllResults();
    }
}