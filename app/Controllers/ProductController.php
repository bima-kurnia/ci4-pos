<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\StockMovementModel;
use CodeIgniter\Controller;

class ProductController extends Controller
{
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected StockMovementModel $stockModel;

    public function __construct()
    {
        $this->productModel  = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->stockModel    = new StockMovementModel();

        helper(['form', 'url']);
    }

    public function index()
    {
        $search   = $this->request->getGet('search');
        $category = $this->request->getGet('category');

        $builder = $this->productModel->select('products.*, categories.name AS category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->orderBy('products.name', 'ASC');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.sku', $search)
                ->groupEnd();
        }

        if ($category) {
            $builder->where('products.category_id', $category);
        }

        return view('products/index', [
            'title'      => 'Products',
            'products'   => $builder->findAll(),
            'categories' => $this->categoryModel->orderBy('name')->findAll(),
            'search'     => $search,
            'category'   => $category,
        ]);
    }

    public function create()
    {
        return view('products/form', [
            'title'      => 'Add Product',
            'product'    => null,
            'categories' => $this->categoryModel->orderBy('name')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'        => 'required|min_length[2]|max_length[150]',
            'sku'         => 'required|max_length[100]|is_unique[products.sku]',
            'price'       => 'required|numeric|greater_than_equal_to[0]',
            'stock'       => 'required|integer|greater_than_equal_to[0]',
            'category_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'sku'         => strtoupper($this->request->getPost('sku')),
            'price'       => $this->request->getPost('price'),
            'stock'       => $this->request->getPost('stock'),
            'category_id' => $this->request->getPost('category_id') ?: null,
        ];

        $productId = $this->productModel->insert($data, true);

        // Record initial stock movement
        if ($data['stock'] > 0) {
            $this->stockModel->record($productId, 'in', $data['stock'], 'Initial stock');
        }

        return redirect()->to('/products')->with('success', 'Product created successfully.');
    }

    public function edit(int $id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        return view('products/form', [
            'title'      => 'Edit Product',
            'product'    => $product,
            'categories' => $this->categoryModel->orderBy('name')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $skuRule = "required|max_length[100]|is_unique[products.sku,id,{$id}]";
        $rules = [
            'name'        => 'required|min_length[2]|max_length[150]',
            'sku'         => $skuRule,
            'price'       => 'required|numeric|greater_than_equal_to[0]',
            'stock'       => 'required|integer|greater_than_equal_to[0]',
            'category_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newStock = (int) $this->request->getPost('stock');
        $oldStock = (int) $product['stock'];
        $diff     = $newStock - $oldStock;

        $this->productModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'sku'         => strtoupper($this->request->getPost('sku')),
            'price'       => $this->request->getPost('price'),
            'stock'       => $newStock,
            'category_id' => $this->request->getPost('category_id') ?: null,
        ]);

        // Record stock adjustment
        if ($diff !== 0) {
            $type = $diff > 0 ? 'in' : 'out';
            $this->stockModel->record($id, $type, abs($diff), 'Manual adjustment');
        }

        return redirect()->to('/products')->with('success', 'Product updated successfully.');
    }

    public function delete(int $id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $this->productModel->delete($id);

        return redirect()->to('/products')->with('success', 'Product deleted successfully.');
    }

    /**
     * AJAX: search products for POS
     */
    public function search()
    {
        $keyword  = $this->request->getGet('q') ?? '';
        $products = $this->productModel->searchForPos($keyword);

        return $this->response->setJSON($products);
    }

    /**
     * AJAX: get single product by ID
     */
    public function getProduct(int $id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return $this->response->setJSON(['error' => 'Not found'], 404);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Stock movements log
     */
    public function stockLog(int $id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        
        return view('products/stock_log', [
            'title'    => 'Stock Log: ' . $product['name'],
            'product'  => $product,
            'movements'=> $this->stockModel->getByProduct($id),
        ]);
    }
}