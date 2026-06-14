<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'        => 'required|min_length[3]|max_length[100]|is_unique[categories.name,id,{id}]',
        'description' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Category name is required.',
            'is_unique' => 'Category name already exists.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
