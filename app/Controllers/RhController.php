<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;

class RhController extends BaseController
{
    public function index()
    {
        return redirect()->to('rh/demandes');
    }

    public function demandes()
    {
        $user = $this->getUserWithDepartement();
        $statutParam = $this->request->getGet('statut');
        $statut = $statutParam === null ? 'en_attente' : (string) $statutParam;

        $departementParam = $this->request->getGet('departement_id');
        $departementId = $departementParam !== null && $departementParam !== '' ? (int) $departementParam : null;

        $congeModel = new CongeModel();
        $conges = $congeModel->getForRh($statut, $departementId);
        $stats = $congeModel->countByStatusForRh($departementId);

        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        $soldeModel = new SoldeModel();
        foreach ($conges as &$conge) {
            $conge['solde_restant'] = null;
            $conge['solde_insuffisant'] = false;
            $conge['can_approve'] = $conge['statut'] === 'en_attente';

            if ((int) $conge['deductible'] === 1) {
                $annee = (int) date('Y', strtotime($conge['date_debut']));
                $solde = $soldeModel->findByEmployeTypeYear((int) $conge['employe_id'], (int) $conge['type_conge_id'], $annee);
                if ($solde === null) {
                    $conge['solde_insuffisant'] = true;
                    $conge['can_approve'] = false;
                } else {
                    $restant = (int) $solde['jours_attribues'] - (int) $solde['jours_pris'];
                    $conge['solde_restant'] = $restant;
                    if ($restant < (int) $conge['nb_jours']) {
                        $conge['solde_insuffisant'] = true;
                        $conge['can_approve'] = false;
                    }
                }
            }
        }
        unset($conge);

        return view('rh/index', [
            'title' => 'Demandes à traiter — RH',
            'user' => $user,
            'conges' => $conges,
            'stats' => $stats,
            'departements' => $departements,
            'statutFilter' => $statut,
            'departementFilter' => $departementId,
            'pendingCount' => $this->getPendingCount(),
        ]);
    }

    public function decision(int $id)
    {
        $user = $this->getUserWithDepartement();
        $decision = (string) ($this->request->getPost('decision') ?? '');
        $commentaire = trim((string) ($this->request->getPost('commentaire') ?? ''));

        if (! in_array($decision, ['approuver', 'refuser'], true)) {
            return redirect()->back()->with('error', 'Action invalide.');
        }

        $congeModel = new CongeModel();
        $conge = $congeModel
            ->select('conges.*, types_conge.deductible')
            ->join('types_conge', 'types_conge.id = conges.type_conge_id')
            ->where('conges.id', $id)
            ->first();

        if ($conge === null) {
            return redirect()->back()->with('error', 'Demande introuvable.');
        }

        if ($conge['statut'] !== 'en_attente') {
            return redirect()->back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $soldeModel = new SoldeModel();
        $solde = null;
        if ($decision === 'approuver' && (int) $conge['deductible'] === 1) {
            $annee = (int) date('Y', strtotime($conge['date_debut']));
            $solde = $soldeModel->findByEmployeTypeYear((int) $conge['employe_id'], (int) $conge['type_conge_id'], $annee);
            if ($solde === null) {
                return redirect()->back()->with('error', 'Solde introuvable pour cet employé.');
            }
            $restant = (int) $solde['jours_attribues'] - (int) $solde['jours_pris'];
            if ($restant < (int) $conge['nb_jours']) {
                return redirect()->back()->with('error', 'Solde insuffisant pour approuver cette demande.');
            }
        }

        $db = db_connect();
        $db->transStart();

        if ($decision === 'approuver') {
            if ((int) $conge['deductible'] === 1 && $solde !== null) {
                $newPris = (int) $solde['jours_pris'] + (int) $conge['nb_jours'];
                $soldeModel->update((int) $solde['id'], ['jours_pris' => $newPris]);
            }

            $congeModel->update($id, [
                'statut' => 'approuvee',
                'commentaire_rh' => $commentaire,
                'traite_par' => (int) $user['id'],
            ]);

            $message = 'Demande approuvée. Le solde a été mis à jour.';
        } else {
            $congeModel->update($id, [
                'statut' => 'refusee',
                'commentaire_rh' => $commentaire,
                'traite_par' => (int) $user['id'],
            ]);

            $message = 'Demande refusée.';
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors du traitement.');
        }

        return redirect()->back()->with('success', $message);
    }

    public function soldes()
    {
        $user = $this->getUserWithDepartement();
        $departementParam = $this->request->getGet('departement_id');
        $departementId = $departementParam !== null && $departementParam !== '' ? (int) $departementParam : null;

        $soldeModel = new SoldeModel();
        $years = $soldeModel->getAvailableYears();
        $yearParam = $this->request->getGet('annee');
        $year = $yearParam !== null && $yearParam !== '' ? (int) $yearParam : ($years[0] ?? (int) date('Y'));

        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        $rows = $soldeModel->getByYearAndDepartement($year, $departementId);

        return view('rh/soldes', [
            'title' => 'Soldes employés — RH',
            'user' => $user,
            'rows' => $rows,
            'departements' => $departements,
            'departementFilter' => $departementId,
            'year' => $year,
            'years' => $years,
            'pendingCount' => $this->getPendingCount(),
        ]);
    }

    private function getUserWithDepartement(): array
    {
        $sessionUser = session()->get('user');
        if ($sessionUser === null) {
            return [];
        }

        $employe = (new EmployeModel())->findWithDepartement((int) $sessionUser['id']);
        return $employe ?? $sessionUser;
    }

    private function getPendingCount(): int
    {
        $stats = (new CongeModel())->countByStatusForRh();
        return $stats['en_attente'] ?? 0;
    }
}
