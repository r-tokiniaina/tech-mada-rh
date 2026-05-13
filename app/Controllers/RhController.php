<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RhController extends BaseController
{
    public function demandesATraiter()
    {
        $statut = $this->request->getGet('statut') ?? null;
        $departementId = $this->request->getGet('departement') ?? null;
        $action = $this->request->getGet('action') ?? null;
        if ($action) {
            $actionId = $this->request->getGet('id') ?? null;
            if (! $actionId) {
                $action = null;
            }
        }

        $nbParStatut = model('CongeModel')->groupBy('statut')->selectCount('id', 'nb')->findAll();
        $total = model('CongeModel')->selectCount('id', 'nb')->findAll();

        $congeModel = model('CongeModel');
        $congeModel->like('statut', $statut);
        if ($departementId) {
            $congeModel->whereDepartement($departementId);
        }
        $congeModel->join('soldes', "soldes.employe_id = conges.employe_id and soldes.type_conge_id = conges.type_conge_id and soldes.annee = strftime('%Y', conges.date_debut)", 'left')
                   ->join('types_conge', "types_conge.id = conges.type_conge_id")
                   ->join('employes AS traite_par_employe', "traite_par_employe.id = conges.traite_par", 'left');

        $congeModel->select('conges.*, jours_attribues - jours_pris as jours_restants, traite_par_employe.nom as nom_traite_par, traite_par_employe.prenom as prenom_traite_par');
        $conges = $congeModel->findAll();

        $actionConge = null;
        foreach ($conges as $conge) {
            if ($conge['id'] == $actionId) {
                $actionConge = $conge;
                break;
            }
        }

        return view('rh/index', [
            'conges' => $conges,
            'nbParStatut' => $nbParStatut,
            'total' => $total,
            'action' => $action,
            'actionConge' => $actionConge,
        ]);
    }

    public function approuverDemande()
    {
        //
    }

    public function refuserDemande()
    {
        //
    }
}
