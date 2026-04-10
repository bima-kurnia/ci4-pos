<?php

namespace App\Models;

use CodeIgniter\Model;

class DiscountModel extends Model
{
    protected $table         = 'discounts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'type', 'value'];
    protected $useTimestamps = false;

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[100]',
        'type'  => 'required|in_list[percentage,fixed]',
        'value' => 'required|numeric|greater_than_equal_to[0]',
    ];

    /**
     * Calculate discount amount given a subtotal
     */
    public function calculate(array $discount, float $subtotal): float
    {
        if ($discount['type'] === 'percentage') {
            return round($subtotal * ($discount['value'] / 100), 2);
        }
        return min((float) $discount['value'], $subtotal);
    }
}