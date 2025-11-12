<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "Seeding Products...\n";
        $this->call('ProductSeeder');

        echo "Seeding Transactions...\n";
        $this->call('TransactionSeeder');

        echo "Seeding Users...\n";
        $this->call('UserSeeder');
    }
}
