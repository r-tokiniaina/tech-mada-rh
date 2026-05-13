<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TypeCongeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'libelle' => 'Congé annuel',
                'jours_annuels' => 30,
                'deductible' => 1
            ],
            [
                'libelle' => 'Maladie',
                'jours_annuels' => 15,
                'deductible' => 0
            ],
            [
                'libelle' => 'Maternité',
                'jours_annuels' => 90,
                'deductible' => 0
            ],
        ];

        $this->db->table('types_conge')->insertBatch($data);
    }
}
