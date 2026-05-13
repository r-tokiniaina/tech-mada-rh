<?php

namespace App\Models;

use CodeIgniter\Model;

class CongeModel extends Model
{
    protected $table            = 'conges';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'type_conge_id',
        'date_debut',
        'date_fin',
        'nb_jours',
        'motif',
        'statut',
        'commentaire_rh',
        'created_at',
        'traite_par'
    ];

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
        'date_debut' => 'required|valid_date[Y-m-d]',
        'date_fin' => 'required|valid_date[Y-m-d]',
        'nb_jours' => 'required|is_natural_no_zero',
        'statut' => 'required|in_list[en_attente,approuvee,refusee,annulee]'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getByEmploye(int $employeId, ?string $statut = null): array
    {
        $builder = $this->select('conges.*, types_conge.libelle as type_libelle')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->where('conges.employe_id', $employeId)
            ->orderBy('conges.created_at', 'DESC');

        if ($statut !== null && $statut !== '') {
            $builder->where('conges.statut', $statut);
        }

        return $builder->findAll();
    }

    public function getRecentByEmploye(int $employeId, int $limit = 5): array
    {
        return $this->select('conges.*, types_conge.libelle as type_libelle')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->where('conges.employe_id', $employeId)
            ->orderBy('conges.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function countByStatus(int $employeId): array
    {
        $rows = $this->select('statut, COUNT(*) as total')
            ->where('employe_id', $employeId)
            ->groupBy('statut')
            ->findAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['statut']] = (int) $row['total'];
        }

        return $stats;
    }

    public function hasOverlap(int $employeId, string $dateDebut, string $dateFin): bool
    {
        $count = $this->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'approuvee'])
            ->groupStart()
            ->where('date_debut <=', $dateFin)
            ->where('date_fin >=', $dateDebut)
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    public function getForRh(?string $statut = null, ?int $departementId = null): array
    {
        $builder = $this->select('conges.*, employes.nom, employes.prenom, employes.departement_id, departements.nom as departement_nom, types_conge.libelle as type_libelle, types_conge.deductible')
            ->join('employes', 'employes.id = conges.employe_id')
            ->join('departements', 'departements.id = employes.departement_id', 'left')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->orderBy('conges.created_at', 'DESC');

        if ($statut !== null && $statut !== '') {
            $builder->where('conges.statut', $statut);
        }

        if ($departementId !== null) {
            $builder->where('employes.departement_id', $departementId);
        }

        return $builder->findAll();
    }

    public function countByStatusForRh(?int $departementId = null): array
    {
        $builder = $this->select('conges.statut, COUNT(*) as total')
            ->join('employes', 'employes.id = conges.employe_id')
            ->groupBy('conges.statut');

        if ($departementId !== null) {
            $builder->where('employes.departement_id', $departementId);
        }

        $rows = $builder->findAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['statut']] = (int) $row['total'];
        }

        return $stats;
    }

    public function getRecentForAdmin(int $limit = 5): array
    {
        return $this->select('conges.*, employes.nom, employes.prenom, types_conge.libelle as type_libelle')
            ->join('employes', 'employes.id = conges.employe_id')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->orderBy('conges.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getAbsencesForMonth(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        return $this->select('conges.*, employes.nom, employes.prenom, types_conge.libelle as type_libelle')
            ->join('employes', 'employes.id = conges.employe_id')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->where('conges.statut', 'approuvee')
            ->groupStart()
            ->where('conges.date_debut <=', $end)
            ->where('conges.date_fin >=', $start)
            ->groupEnd()
            ->orderBy('conges.date_debut', 'ASC')
            ->findAll();
    }

    public function countAbsentsOnDate(string $date): int
    {
        return $this->where('statut', 'approuvee')
            ->where('date_debut <=', $date)
            ->where('date_fin >=', $date)
            ->countAllResults();
    }

    public function countByStatusForAdmin(): array
    {
        $rows = $this->select('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->findAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['statut']] = (int) $row['total'];
        }

        return $stats;
    }

    public function countApprovedForMonth(int $year, int $month): int
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        return $this->where('statut', 'approuvee')
            ->where('date_debut >=', $start)
            ->where('date_debut <=', $end)
            ->countAllResults();
    }

    public function getForAdmin(?string $statut = null, ?int $departementId = null): array
    {
        $builder = $this->select('conges.*, employes.nom, employes.prenom, employes.departement_id, departements.nom as departement_nom, types_conge.libelle as type_libelle, traite.nom as traite_nom, traite.prenom as traite_prenom')
            ->join('employes', 'employes.id = conges.employe_id')
            ->join('departements', 'departements.id = employes.departement_id', 'left')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->join('employes as traite', 'traite.id = conges.traite_par', 'left')
            ->orderBy('conges.created_at', 'DESC');

        if ($statut !== null && $statut !== '') {
            $builder->where('conges.statut', $statut);
        }

        if ($departementId !== null) {
            $builder->where('employes.departement_id', $departementId);
        }

        return $builder->findAll();
    }
}
