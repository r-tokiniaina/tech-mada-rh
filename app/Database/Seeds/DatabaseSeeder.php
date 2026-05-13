<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('DepartementSeeder');
        $this->call('TypeCongeSeeder');
        $this->call('EmployeSeeder');
        $this->call('SoldeSeeder');
        $this->call('CongeSeeder');
    }
}
