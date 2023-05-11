<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitSeeder extends Seeder
{
    public function run()
    {
        $data[] = [
            'name' => 'premium',
        ];

        $this->db->table('plans')->insertBatch($data);

        $data = [
            'name' => 'Jônatas Alves',
            'username' => 'jonatas',
            'password' => password_hash('102030',PASSWORD_DEFAULT),
            'email' => 'alvesjonatas99@gmail.com',
            'plan_id' => 1,
            'auth_2fa' => false,
            'active' => true,
        ];
        $this->db->table('users')->insert($data);

    }
}