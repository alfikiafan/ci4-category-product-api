<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categories = $this->db->table('categories')->get()->getResultArray();
        $categoryIds = array_column($categories, 'id', 'name');

        $products = [
            [
                'category_id' => $categoryIds['Electronics'],
                'name'        => 'Wireless Mouse',
                'sku'         => 'ELEC-001',
                'price'       => 125000,
                'stock'       => 50,
                'description' => '2.4GHz wireless optical mouse',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'category_id' => $categoryIds['Electronics'],
                'name'        => 'USB-C Charger 20W',
                'sku'         => 'ELEC-002',
                'price'       => 99000,
                'stock'       => 100,
                'description' => 'Fast charging USB-C power adapter',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'category_id' => $categoryIds['Groceries'],
                'name'        => 'Beras Premium 5kg',
                'sku'         => 'GROC-001',
                'price'       => 68000,
                'stock'       => 200,
                'description' => 'Beras premium kemasan 5kg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'category_id' => $categoryIds['Stationery'],
                'name'        => 'Ballpoint Pen (12 pcs)',
                'sku'         => 'STAT-001',
                'price'       => 24000,
                'stock'       => 300,
                'description' => 'Pack of 12 blue ballpoint pens',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('products')->insertBatch($products);
    }
}
