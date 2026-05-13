<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SoldeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'employe_id' => 1,
                'type_conge_id' => 1,
                'annee' => 2025,
                'jours_attribues' => 30,
                'jours_pris' => 5
            ],
            [
                'employe_id' => 2,
                'type_conge_id' => 1,
                'annee' => 2025,
                'jours_attribues' => 30,
                'jours_pris' => 10
            ],
            [
                'employe_id' => 3,
                'type_conge_id' => 2,
                'annee' => 2025,
                'jours_attribues' => 15,
                'jours_pris' => 2
            ],
        ];

        $this->db->table('soldes')->insertBatch($data);
    }
}
