<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'phone', 'email', 'address', 'created_at'];

    // Timestamps handled manually - table no has no updated_at
    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]',
    ];
}