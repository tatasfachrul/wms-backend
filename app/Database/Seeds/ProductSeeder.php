<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Kardus Besar',
                'sku' => 'KB001',
                'stock' => 100,
                'shelf_location' => 'A1',
                'minimum_stock' => 10,
            ],
            [
                'name' => 'Plastik Kecil',
                'sku' => 'PK002',
                'stock' => 200,
                'shelf_location' => 'B2',
                'minimum_stock' => 20,
            ],
            [
                'name' => 'Botol 1L',
                'sku' => 'BT003',
                'stock' => 50,
                'shelf_location' => 'C3',
                'minimum_stock' => 5,
            ],
        ];

        $this->db->table('products')->insertBatch($data);
    }
}
