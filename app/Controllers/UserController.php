<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class UserController extends Controller
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('users/index', [
            'title' => 'Users',
            'users' => $this->userModel->orderBy('name')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,cashier]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $this->userModel->insert([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->userModel->hashPassword($this->request->getPost('password')),
            'role'     => $this->request->getPost('role'),
        ]);
        
        return redirect()->to('/users')->with('success', 'User created successfully.');
    }

    public function update(int $id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        $emailRule = "required|valid_email|is_unique[users.email,id,{$id}]";

        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => $emailRule,
            'role'  => 'required|in_list[admin,cashier]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $this->request->getPost('role'),
        ];

        $newPass = $this->request->getPost('password');

        if ($newPass) {
            $data['password'] = $this->userModel->hashPassword($newPass);
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/users')->with('success', 'User updated successfully.');
    }

    public function delete(int $id)
    {
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'You cannot delete your own account.');
        }

        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', 'User deleted.');
    }
}