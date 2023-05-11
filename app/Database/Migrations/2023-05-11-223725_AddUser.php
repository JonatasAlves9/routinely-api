<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'plan_id' => [
                'type' => 'INT',
            ],
            'auth_2fa' => [
                'type' => 'BOOLEAN',
            ],
            'secret_2fa' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'force_change_passw' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'reset_password_token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'reset_password_expires' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at timestamp DEFAULT current_timestamp NOT NULL',
            'created_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'updated_at timestamp',
            'updated_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'deleted_at timestamp',
            'deleted_by' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('username');
        $this->forge->addKey('name');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'RESTRICT');
        // $this->forge->addForeignKey('seller_id','sellers','id','CASCADE','SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('deleted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
