<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'null' => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'unique' => true
            ],
            'stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'shelf_location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'minimum_stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('products', true);
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
