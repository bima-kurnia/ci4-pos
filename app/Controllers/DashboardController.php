<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $transactionModel = new TransactionModel();
        $productModel     = new ProductModel();
        $customerModel    = new CustomerModel();
        $userModel        = new UserModel();

        // Low-stock threshold
        $lowStock = $productModel->select('products.*, categories.name AS category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.stock <=', 10)
            ->orderBy('products.stock', 'ASC')
            ->findAll(5);

        $data = [
            'title'           => 'Dashboard',
            'today_revenue'   => $transactionModel->getTodayRevenue(),
            'today_count'     => $transactionModel->getTodayCount(),
            'total_products'  => $productModel->countAll(),
            'total_customers' => $customerModel->countAll(),
            'total_users'     => $userModel->countAll(),
            'recent_transactions' => $transactionModel->getWithDetails(),
            'daily_summary'   => $transactionModel->getDailySummary(),
            'low_stock'       => $lowStock,
        ];

        // Slice recent to 5
        $data['recent_transactions'] = array_slice($data['recent_transactions'], 0, 5);

        return view('dashboard/index', $data);
    }
}