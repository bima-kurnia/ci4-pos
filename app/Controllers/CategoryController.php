<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use CodeIgniter\Controller;

class CategoryController extends Controller
{
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();

        helper(['form', 'url']);
    }

    public function index()
    {
        return view('categories/index', [
            'title'      => 'Categories',
            'categories' => $this->categoryModel->orderBy('name')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = ['name' => 'required|min_length[2]|max_length[100]|is_unique[categories.name]'];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->categoryModel->insert(['name' => $this->request->getPost('name')]);

        return redirect()->to('/categories')->with('success', 'Category added successfully.');
    }

    public function update(int $id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Category not found.');
        }

        $nameRule = "required|min_length[2]|max_length[100]|is_unique[categories.name,id,{$id}]";

        if (!$this->validate(['name' => $nameRule])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->categoryModel->update($id, ['name' => $this->request->getPost('name')]);

        return redirect()->to('/categories')->with('success', 'Category updated successfully.');
    }

    public function delete(int $id)
    {
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            return redirect()->to('/categories')->with('error', 'Category not found.');
        }

        $this->categoryModel->delete($id);

        return redirect()->to('/categories')->with('success', 'Category deleted.');
    }
}