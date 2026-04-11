<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\Controller;

class CustomerController extends Controller
{
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();

        helper(['form', 'url']);
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $builder = $this->customerModel->orderBy('name');

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('phone', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        return view('customers/index', [
            'title'     => 'Customers',
            'customers' => $builder->findAll(),
            'search'    => $search,
        ]);
    }

    public function store()
    {
        $rules = [
            'name'  => 'required|min_length[2]|max_length[150]',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->customerModel->insert([
            'name'    => $this->request->getPost('name'),
            'phone'   => $this->request->getPost('phone') ?: null,
            'email'   => $this->request->getPost('email') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ]);

        return redirect()->to('/customers')->with('success', 'Customer added successfully.');
    }

    public function update(int $id)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        $rules = [
            'name'  => 'required|min_length[2]|max_length[150]',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->customerModel->update($id, [
            'name'    => $this->request->getPost('name'),
            'phone'   => $this->request->getPost('phone') ?: null,
            'email'   => $this->request->getPost('email') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ]);

        return redirect()->to('/customers')->with('success', 'Customer updated successfully.');
    }

    public function delete(int $id)
    {
        $this->customerModel->delete($id);

        return redirect()->to('/customers')->with('success', 'Customer deleted.');
    }

    /** AJAX: search customers for POS dropdown */
    public function search()
    {
        $q = $this->request->getGet('q') ?? '';

        $results = $this->customerModel
            ->groupStart()
            ->like('name', $q)
            ->orLike('phone', $q)
            ->groupEnd()
            ->findAll(10);

        return $this->response->setJSON($results);
    }
}