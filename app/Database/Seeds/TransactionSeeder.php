<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'product_id' => 1,
                'type' => 'IN',
                'quantity' => 10,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_id' => 2,
                'type' => 'OUT',
                'quantity' => 5,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_id' => 3,
                'type' => 'IN',
                'quantity' => 20,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('transactions')->insertBatch($data);
    }
}
