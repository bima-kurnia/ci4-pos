<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'sku', 'price', 'stock', 'category_id', 'image'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[150]',
        'sku'   => 'required|max_length[100]',
        'price' => 'required|numeric|greater_than_equal_to[0]',
        'stock' => 'required|integer|greater_than_equal_to[0]',
    ];

    /**
     * Get all products with their category name
     */
    public function getWithCategory(): array
    {
        return $this->select('products.*, categories.name AS category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->orderBy('products.name', 'ASC')
            ->findAll();
    }

    /**
     * Get single product with category
     */
    public function getOneWithCategory(int $id): ?array
    {
        return $this->select('products.*, categories.name AS category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->find($id);
    }

    /**
     * Search products for POS cart (by name or SKU)
     */
    public function searchForPos(string $keyword): array
    {
        return $this->select('products.*, categories.name AS category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->groupStart()
                ->like('products.name', $keyword)
                ->orLike('products.sku', $keyword)
            ->groupEnd()
            ->where('products.stock >', 0)
            ->orderBy('products.name', 'ASC')
            ->findAll();
    }

    /**
     * Reduce stock — returns false if insufficient
     */
    public function reduceStock(int $productId, int $qty): bool
    {
        $product = $this->find($productId);
        if (!$product || $product['stock'] < $qty) {
            return false;
        }
        $this->update($productId, ['stock' => $product['stock'] - $qty]);
        return true;
    }

    /**
     * Increase stock (for returns / adjustments)
     */
    public function increaseStock(int $productId, int $qty): void
    {
        $product = $this->find($productId);
        if ($product) {
            $this->update($productId, ['stock' => $product['stock'] + $qty]);
        }
    }
}