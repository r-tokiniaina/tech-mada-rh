<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'nom' => ['type' => 'VARCHAR', 'constraint' => 30],
            'prenom' => ['type' => 'VARCHAR', 'constraint' => 30],
            'email' => ['type' => 'VARCHAR', 'constraint' => 30],
            'password' => ['type' => 'VARCHAR', 'constraint' => 100],
            'role' => ['type' => 'VARCHAR', 'constraint' => 7],
            'departement_id' => ['type' => 'INTEGER'],
            'date_embauche' => ['type' => 'DATE'],
            'actif' => ['type' => 'INTEGER'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email', false, true);
        $this->forge->addForeignKey('departement_id', 'departements', 'id');
        $this->forge->createTable('employes');
    }

    public function down()
    {
        $this->forge->dropTable('employes');
    }
}
