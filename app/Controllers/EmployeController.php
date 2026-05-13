<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;
use App\Models\TypeCongeModel;
use DateTime;

class EmployeController extends BaseController
{
    public function index()
    {
        return redirect()->to('employe/dashboard');
    }

    public function dashboard()
    {
        $user = $this->getUserWithDepartement();
        $year = (int) date('Y');

        $soldeModel = new SoldeModel();
        $congeModel = new CongeModel();

        $soldes = $soldeModel->getByEmployeAndYear((int) $user['id'], $year);
        if (empty($soldes)) {
            $latestYear = $soldeModel->getLatestYearForEmploye((int) $user['id']);
            if ($latestYear !== null) {
                $year = $latestYear;
                $soldes = $soldeModel->getByEmployeAndYear((int) $user['id'], $year);
            }
        }
        $stats = $congeModel->countByStatus((int) $user['id']);
        $recentConges = $congeModel->getRecentByEmploye((int) $user['id'], 5);

        $totalAttribues = 0;
        $totalRestants = 0;
        foreach ($soldes as &$solde) {
            $solde['jours_restants'] = max(0, (int) $solde['jours_attribues'] - (int) $solde['jours_pris']);
            $solde['ratio'] = $solde['jours_attribues'] > 0
                ? (int) round(($solde['jours_restants'] / $solde['jours_attribues']) * 100)
                : 0;
            $totalAttribues += (int) $solde['jours_attribues'];
            $totalRestants += (int) $solde['jours_restants'];
        }
        unset($solde);

        return view('employe/dashboard', [
            'title' => 'Tableau de bord — Employé',
            'user' => $user,
            'soldes' => $soldes,
            'recentConges' => $recentConges,
            'stats' => $stats,
            'totalAttribues' => $totalAttribues,
            'totalRestants' => $totalRestants,
            'year' => $year,
            'pendingCount' => $stats['en_attente'] ?? 0,
        ]);
    }

    public function create()
    {
        $user = $this->getUserWithDepartement();
        $year = (int) date('Y');

        $typeModel = new TypeCongeModel();
        $soldeModel = new SoldeModel();

        $types = $typeModel->orderBy('libelle', 'ASC')->findAll();
        $soldes = $soldeModel->getByEmployeAndYear((int) $user['id'], $year);
        if (empty($soldes)) {
            $latestYear = $soldeModel->getLatestYearForEmploye((int) $user['id']);
            if ($latestYear !== null) {
                $year = $latestYear;
                $soldes = $soldeModel->getByEmployeAndYear((int) $user['id'], $year);
            }
        }
        $soldesByType = [];
        foreach ($soldes as $solde) {
            $soldesByType[$solde['type_conge_id']] = $solde;
        }

        $computedDays = null;
        $rangeLabel = null;
        $dateDebut = old('date_debut');
        $dateFin = old('date_fin');
        if ($dateDebut && $dateFin && strtotime($dateDebut) <= strtotime($dateFin)) {
            $computedDays = $this->calculateWorkingDays($dateDebut, $dateFin);
            $rangeLabel = sprintf('du %s au %s', date('d/m/Y', strtotime($dateDebut)), date('d/m/Y', strtotime($dateFin)));
        }

        return view('employe/create', [
            'title' => 'Nouvelle demande — Employé',
            'user' => $user,
            'types' => $types,
            'soldes' => $soldes,
            'soldesByType' => $soldesByType,
            'year' => $year,
            'computedDays' => $computedDays,
            'rangeLabel' => $rangeLabel,
            'pendingCount' => $this->getPendingCount($user['id']),
        ]);
    }

    public function store()
    {
        $user = $this->getUserWithDepartement();

        $data = [
            'employe_id' => (int) $user['id'],
            'type_conge_id' => (int) ($this->request->getPost('type_conge_id') ?? 0),
            'date_debut' => (string) ($this->request->getPost('date_debut') ?? ''),
            'date_fin' => (string) ($this->request->getPost('date_fin') ?? ''),
            'motif' => trim((string) ($this->request->getPost('motif') ?? '')),
        ];

        $errors = [];
        if ($data['type_conge_id'] <= 0) {
            $errors['type_conge_id'] = 'Veuillez sélectionner un type de congé.';
        }
        if ($data['date_debut'] === '') {
            $errors['date_debut'] = 'La date de début est obligatoire.';
        }
        if ($data['date_fin'] === '') {
            $errors['date_fin'] = 'La date de fin est obligatoire.';
        }
        if ($data['date_debut'] !== '' && $data['date_fin'] !== '' && strtotime($data['date_debut']) > strtotime($data['date_fin'])) {
            $errors['date_fin'] = 'La date de fin doit être postérieure ou égale à la date de début.';
        }

        $congeModel = new CongeModel();
        if (empty($errors) && $congeModel->hasOverlap((int) $user['id'], $data['date_debut'], $data['date_fin'])) {
            $errors['date_debut'] = 'Une demande active chevauche déjà ces dates.';
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $nbJours = $this->calculateWorkingDays($data['date_debut'], $data['date_fin']);
        if ($nbJours <= 0) {
            return redirect()->back()->withInput()->with('errors', ['date_debut' => 'Le nombre de jours calculé est invalide.']);
        }

        $data['nb_jours'] = $nbJours;
        $data['statut'] = 'en_attente';
        $data['commentaire_rh'] = '';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['traite_par'] = null;

        if (! $congeModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $congeModel->errors());
        }

        return redirect()->to('employe/dashboard')->with('success', 'Votre demande de congé a bien été soumise.');
    }

    public function mesConges()
    {
        $user = $this->getUserWithDepartement();
        $statut = (string) ($this->request->getGet('statut') ?? '');

        $congeModel = new CongeModel();
        $conges = $congeModel->getByEmploye((int) $user['id'], $statut !== '' ? $statut : null);

        return view('employe/index', [
            'title' => 'Mes demandes — Employé',
            'user' => $user,
            'conges' => $conges,
            'statutFilter' => $statut,
            'pendingCount' => $this->getPendingCount($user['id']),
        ]);
    }

    public function cancel(int $id)
    {
        $user = $this->getUserWithDepartement();
        $congeModel = new CongeModel();

        $conge = $congeModel
            ->where('id', $id)
            ->where('employe_id', (int) $user['id'])
            ->first();

        if ($conge === null) {
            return redirect()->back()->with('error', 'Demande introuvable.');
        }

        if ($conge['statut'] !== 'en_attente') {
            return redirect()->back()->with('error', 'Seules les demandes en attente peuvent être annulées.');
        }

        $congeModel->update($id, [
            'statut' => 'annulee',
            'commentaire_rh' => "Annulée par l'employé",
        ]);

        return redirect()->back()->with('success', 'La demande a été annulée.');
    }

    public function profile()
    {
        $user = $this->getUserWithDepartement();

        return view('employe/profile', [
            'title' => 'Mon profil — Employé',
            'user' => $user,
            'pendingCount' => $this->getPendingCount($user['id']),
        ]);
    }

    public function updateProfile()
    {
        $user = $this->getUserWithDepartement();

        $nom = trim((string) ($this->request->getPost('nom') ?? ''));
        $prenom = trim((string) ($this->request->getPost('prenom') ?? ''));
        $password = (string) ($this->request->getPost('password') ?? '');
        $passwordConfirm = (string) ($this->request->getPost('password_confirm') ?? '');

        $errors = [];
        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        }
        if ($password !== '') {
            if (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            if ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'La confirmation du mot de passe ne correspond pas.';
            }
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
        ];
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $employeModel = new EmployeModel();
        if (! $employeModel->update((int) $user['id'], $data)) {
            return redirect()->back()->withInput()->with('errors', $employeModel->errors());
        }

        $updatedUser = $employeModel->findWithDepartement((int) $user['id']) ?? $user;
        session()->set('user', $updatedUser);

        return redirect()->back()->with('success', 'Votre profil a été mis à jour.');
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

    private function getPendingCount(int $employeId): int
    {
        $stats = (new CongeModel())->countByStatus($employeId);
        return $stats['en_attente'] ?? 0;
    }

    private function calculateWorkingDays(string $dateDebut, string $dateFin): int
    {
        $start = new DateTime($dateDebut);
        $end = new DateTime($dateFin);
        $days = 0;

        while ($start <= $end) {
            $dayOfWeek = (int) $start->format('N');
            if ($dayOfWeek < 6) {
                $days++;
            }
            $start->modify('+1 day');
        }

        return $days;
    }
}
