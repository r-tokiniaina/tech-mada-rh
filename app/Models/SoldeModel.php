<?php

namespace App\Models;

use CodeIgniter\Model;

class SoldeModel extends Model
{
    protected $table            = 'soldes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['employe_id', 'type_conge_id', 'annee', 'jours_attribues', 'jours_pris'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'employe_id' => 'required|is_natural_no_zero',
        'type_conge_id' => 'required|is_natural_no_zero',
        'annee' => 'required|is_natural_no_zero',
        'jours_attribues' => 'required|is_natural',
        'jours_pris' => 'required|is_natural'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getByEmployeAndYear(int $employeId, int $annee): array
    {
        return $this->select('soldes.*, types_conge.libelle, types_conge.deductible, types_conge.jours_annuels')
            ->join('types_conge', 'types_conge.id = soldes.type_conge_id')
            ->where('soldes.employe_id', $employeId)
            ->where('soldes.annee', $annee)
            ->orderBy('types_conge.libelle', 'ASC')
            ->findAll();
    }

    public function getLatestYearForEmploye(int $employeId): ?int
    {
        $row = $this->selectMax('annee')
            ->where('employe_id', $employeId)
            ->first();

        if ($row === null || empty($row['annee'])) {
            return null;
        }

        return (int) $row['annee'];
    }

    public function findByEmployeTypeYear(int $employeId, int $typeCongeId, int $annee): ?array
    {
        return $this->where('employe_id', $employeId)
            ->where('type_conge_id', $typeCongeId)
            ->where('annee', $annee)
            ->first();
    }

    public function getAvailableYears(): array
    {
        $rows = $this->select('annee')
            ->distinct()
            ->orderBy('annee', 'DESC')
            ->findAll();

        return array_map(static fn ($row) => (int) $row['annee'], $rows);
    }

    public function getByYearAndDepartement(int $annee, ?int $departementId = null): array
    {
        $builder = $this->select('soldes.*, employes.nom, employes.prenom, employes.email, employes.departement_id, departements.nom as departement_nom, types_conge.libelle as type_libelle')
            ->join('employes', 'employes.id = soldes.employe_id')
            ->join('departements', 'departements.id = employes.departement_id', 'left')
            ->join('types_conge', 'types_conge.id = soldes.type_conge_id')
            ->where('soldes.annee', $annee)
            ->orderBy('employes.nom', 'ASC')
            ->orderBy('employes.prenom', 'ASC')
            ->orderBy('types_conge.libelle', 'ASC');

        if ($departementId !== null) {
            $builder->where('employes.departement_id', $departementId);
        }

        return $builder->findAll();
    }

    public function getTotalsByEmployeYear(int $annee): array
    {
        $rows = $this->select('employe_id, SUM(jours_attribues) as total_attribues, SUM(jours_pris) as total_pris')
            ->where('annee', $annee)
            ->groupBy('employe_id')
            ->findAll();

        $totals = [];
        foreach ($rows as $row) {
            $totals[(int) $row['employe_id']] = [
                'attribues' => (int) $row['total_attribues'],
                'pris' => (int) $row['total_pris'],
            ];
        }

        return $totals;
    }
}
