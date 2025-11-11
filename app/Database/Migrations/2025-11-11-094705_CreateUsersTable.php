<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Create ENUM type if it doesn't exist
        $result = $db->query("SELECT 1 FROM pg_type WHERE typname = 'role_enum'")->getResult();
        if (empty($result)) {
            $db->query("CREATE TYPE role_enum AS ENUM ('admin', 'staff');");
        }

        $this->forge->addField([
            'id' => ['type' => 'SERIAL', 'null' => false],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'role_enum', 'default' => 'staff', 'null' => false],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
        $db = \Config\Database::connect();
        $db->query('DROP TYPE IF EXISTS role_enum;');
    }
}
