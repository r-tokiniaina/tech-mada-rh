<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nom' => 'RH',
                'description' => 'Ressources humaines'
            ],
            [
                'nom' => 'IT',
                'description' => 'Informatique'
            ],
            [
                'nom' => 'Finance',
                'description' => 'Comptabilité et finance'
            ],
            [
                'nom' => 'Marketing',
                'description' => 'Communication et marketing'
            ],
        ];

        $this->db->table('departements')->insertBatch($data);
    }
}
