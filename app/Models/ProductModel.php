<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['category_id', 'name', 'sku', 'price', 'stock', 'description'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_id' => 'required|integer|is_not_unique[categories.id]',
        'name'        => 'required|min_length[3]|max_length[150]',
        'sku'         => 'required|max_length[50]|is_unique[products.sku,id,{id}]',
        'price'       => 'required|decimal|greater_than_equal_to[0]',
        'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
        'description' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'category_id' => [
            'required'      => 'Category is required.',
            'is_not_unique' => 'Selected category does not exist.',
        ],
        'sku' => [
            'required'  => 'SKU is required.',
            'is_unique' => 'SKU already exists.',
        ],
        'price' => [
            'required' => 'Price is required.',
            'decimal'  => 'Price must be a valid number.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get products joined with category name.
     */
    public function getWithCategory(?int $id = null)
    {
        $builder = $this->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id');

        if ($id !== null) {
            return $builder->find($id);
        }

        return $builder;
    }
}
