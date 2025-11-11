<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'null' => false,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'SERIAL',
                'null' => false,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'comment' => 'IN or OUT',
            ],
            'quantity' => [
                'type' => 'INT',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
