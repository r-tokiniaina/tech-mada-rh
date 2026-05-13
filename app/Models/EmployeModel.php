<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeModel extends Model
{
    protected $table            = 'employes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nom', 'prenom', 'email', 'password', 'role', 'departement_id', 'date_embauche', 'actif'
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
    protected $validationRules      = [
        'nom' => 'required|min_length[2]',
        'prenom' => 'required|min_length[2]',
        'password' => 'permit_empty|min_length[6]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    public function findByEmailAndPassword($email, $password)
    {
        $user = $this->where('email', $email)
            ->first();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function findWithDepartement(int $id): ?array
    {
        return $this->select('employes.*, departements.nom as departement_nom')
            ->join('departements', 'departements.id = employes.departement_id', 'left')
            ->where('employes.id', $id)
            ->first();
    }

    public function getAllWithDepartement(?int $departementId = null, ?string $search = null): array
    {
        $builder = $this->select('employes.*, departements.nom as departement_nom')
            ->join('departements', 'departements.id = employes.departement_id', 'left')
            ->orderBy('employes.actif', 'DESC')
            ->orderBy('employes.nom', 'ASC')
            ->orderBy('employes.prenom', 'ASC');

        if ($departementId !== null) {
            $builder->where('employes.departement_id', $departementId);
        }

        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('employes.nom', $search)
                ->orLike('employes.prenom', $search)
                ->orLike('employes.email', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
