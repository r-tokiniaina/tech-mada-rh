<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmployeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nom' => 'Rakoto',
                'prenom' => 'Jean',
                'email' => 'jean@demo.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'departement_id' => 1,
                'date_embauche' => '2023-01-15',
                'actif' => 1
            ],
            [
                'nom' => 'Rabe',
                'prenom' => 'Marie',
                'email' => 'marie@demo.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'employe',
                'departement_id' => 2,
                'date_embauche' => '2023-03-10',
                'actif' => 1
            ],
            [
                'nom' => 'Andry',
                'prenom' => 'Paul',
                'email' => 'paul@demo.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'rh',
                'departement_id' => 3,
                'date_embauche' => '2022-08-01',
                'actif' => 1
            ],
        ];

        $this->db->table('employes')->insertBatch($data);
    }
}
