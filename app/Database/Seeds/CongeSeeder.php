<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CongeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'employe_id' => 1,
                'type_conge_id' => 1,
                'date_debut' => '2025-07-01',
                'date_fin' => '2025-07-05',
                'nb_jours' => 5,
                'motif' => 'Vacances',
                'statut' => 'approuvee',
                'commentaire_rh' => 'Validé',
                'created_at' => '2025-06-01 09:00:00',
                'traite_par' => 3
            ],
            [
                'employe_id' => 2,
                'type_conge_id' => 2,
                'date_debut' => '2025-06-10',
                'date_fin' => '2025-06-12',
                'nb_jours' => 3,
                'motif' => 'Grippe',
                'statut' => 'en_attente',
                'commentaire_rh' => '',
                'created_at' => '2025-06-05 10:15:00',
                'traite_par' => null
            ],
        ];

        $this->db->table('conges')->insertBatch($data);
    }
}
