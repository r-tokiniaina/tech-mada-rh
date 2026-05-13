<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConges extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'employe_id' => ['type' => 'INTEGER'],
            'type_conge_id' => ['type' => 'INTEGER'],
            'date_debut' => ['type' => 'DATE'],
            'date_fin' => ['type' => 'DATE'],
            'nb_jours' => ['type' => 'INTEGER'],
            'motif' => ['type' => 'VARCHAR', 'constraint' => 255],
            'statut' => ['type' => 'VARCHAR', 'constraint' => 10],
            'commentaire_rh' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME'],
            'traite_par' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id');
        $this->forge->addForeignKey('type_conge_id', 'types_conge', 'id');
        $this->forge->addForeignKey('traite_par', 'employes', 'id');
        $this->forge->createTable('conges');
    }

    public function down()
    {
        $this->forge->dropTable('conges');
    }
}
