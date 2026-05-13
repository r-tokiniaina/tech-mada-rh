<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;
use App\Models\TypeCongeModel;

class AdminController extends BaseController
{
    public function index()
    {
        return redirect()->to('admin/dashboard');
    }

    public function dashboard()
    {
        $user = $this->getUserWithDepartement();
        $year = (int) date('Y');
        $month = (int) date('m');
        $today = date('Y-m-d');

        $employeModel = new EmployeModel();
        $departementModel = new DepartementModel();
        $congeModel = new CongeModel();

        $actifs = $employeModel->where('actif', 1)->countAllResults();
        $departementsCount = $departementModel->countAllResults();
        $stats = $congeModel->countByStatusForAdmin();
        $pending = $stats['en_attente'] ?? 0;
        $approvedThisMonth = $congeModel->countApprovedForMonth($year, $month);
        $absentsToday = $congeModel->countAbsentsOnDate($today);
        $recentConges = $congeModel->getRecentForAdmin(5);
        $absencesMonth = $congeModel->getAbsencesForMonth($year, $month);

        return view('admin/dashboard', [
            'title' => 'Administration — Tableau de bord',
            'user' => $user,
            'actifs' => $actifs,
            'departementsCount' => $departementsCount,
            'pending' => $pending,
            'approvedThisMonth' => $approvedThisMonth,
            'absentsToday' => $absentsToday,
            'recentConges' => $recentConges,
            'absencesMonth' => $absencesMonth,
        ]);
    }

    public function demandes()
    {
        $user = $this->getUserWithDepartement();
        $statutParam = $this->request->getGet('statut');
        $statut = $statutParam !== null ? (string) $statutParam : '';

        $departementParam = $this->request->getGet('departement_id');
        $departementId = $departementParam !== null && $departementParam !== '' ? (int) $departementParam : null;

        $congeModel = new CongeModel();
        $conges = $congeModel->getForAdmin($statut, $departementId);

        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        return view('admin/demandes', [
            'title' => 'Historique des demandes — Admin',
            'user' => $user,
            'conges' => $conges,
            'departements' => $departements,
            'statutFilter' => $statut,
            'departementFilter' => $departementId,
        ]);
    }

    public function employes()
    {
        $user = $this->getUserWithDepartement();
        $departementParam = $this->request->getGet('departement_id');
        $departementId = $departementParam !== null && $departementParam !== '' ? (int) $departementParam : null;
        $search = (string) ($this->request->getGet('q') ?? '');

        $employeModel = new EmployeModel();
        $employes = $employeModel->getAllWithDepartement($departementId, $search);

        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        $currentYear = (int) date('Y');
        $totals = (new SoldeModel())->getTotalsByEmployeYear($currentYear);

        return view('admin/employes', [
            'title' => 'Gestion des employés — Admin',
            'user' => $user,
            'employes' => $employes,
            'departements' => $departements,
            'departementFilter' => $departementId,
            'search' => $search,
            'totals' => $totals,
            'currentYear' => $currentYear,
        ]);
    }

    public function storeEmploye()
    {
        $nom = trim((string) ($this->request->getPost('nom') ?? ''));
        $prenom = trim((string) ($this->request->getPost('prenom') ?? ''));
        $email = trim((string) ($this->request->getPost('email') ?? ''));
        $password = (string) ($this->request->getPost('password') ?? '');
        $departementId = (int) ($this->request->getPost('departement_id') ?? 0);
        $role = (string) ($this->request->getPost('role') ?? 'employe');
        $dateEmbauche = (string) ($this->request->getPost('date_embauche') ?? '');

        $errors = [];
        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        }
        if ($email === '') {
            $errors['email'] = "L'email est obligatoire.";
        }
        if ($password === '' || strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if ($departementId <= 0) {
            $errors['departement_id'] = 'Le département est obligatoire.';
        }
        if (! in_array($role, ['employe', 'rh', 'admin'], true)) {
            $errors['role'] = 'Rôle invalide.';
        }
        if ($dateEmbauche === '') {
            $errors['date_embauche'] = "La date d'embauche est obligatoire.";
        }

        $employeModel = new EmployeModel();
        if ($email !== '' && $employeModel->where('email', $email)->first()) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'departement_id' => $departementId,
            'date_embauche' => $dateEmbauche,
            'actif' => 1,
        ];

        $newId = $employeModel->insert($data, true);
        if (! $newId) {
            return redirect()->back()->withInput()->with('error', "Impossible de créer l'employé.");
        }

        $types = (new TypeCongeModel())->findAll();
        if (! empty($types)) {
            $year = (int) date('Y');
            $soldes = [];
            foreach ($types as $type) {
                $soldes[] = [
                    'employe_id' => (int) $newId,
                    'type_conge_id' => (int) $type['id'],
                    'annee' => $year,
                    'jours_attribues' => (int) $type['jours_annuels'],
                    'jours_pris' => 0,
                ];
            }
            (new SoldeModel())->insertBatch($soldes);
        }

        return redirect()->to('admin/employes')->with('success', "Employé créé avec succès.");
    }

    public function editEmploye(int $id)
    {
        $user = $this->getUserWithDepartement();
        $employe = (new EmployeModel())->findWithDepartement($id);
        if ($employe === null) {
            return redirect()->to('admin/employes')->with('error', 'Employé introuvable.');
        }

        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        return view('admin/employe_edit', [
            'title' => 'Éditer un employé — Admin',
            'user' => $user,
            'employe' => $employe,
            'departements' => $departements,
        ]);
    }

    public function updateEmploye(int $id)
    {
        $nom = trim((string) ($this->request->getPost('nom') ?? ''));
        $prenom = trim((string) ($this->request->getPost('prenom') ?? ''));
        $email = trim((string) ($this->request->getPost('email') ?? ''));
        $password = (string) ($this->request->getPost('password') ?? '');
        $departementId = (int) ($this->request->getPost('departement_id') ?? 0);
        $role = (string) ($this->request->getPost('role') ?? 'employe');
        $dateEmbauche = (string) ($this->request->getPost('date_embauche') ?? '');

        $errors = [];
        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        }
        if ($email === '') {
            $errors['email'] = "L'email est obligatoire.";
        }
        if ($departementId <= 0) {
            $errors['departement_id'] = 'Le département est obligatoire.';
        }
        if (! in_array($role, ['employe', 'rh', 'admin'], true)) {
            $errors['role'] = 'Rôle invalide.';
        }
        if ($dateEmbauche === '') {
            $errors['date_embauche'] = "La date d'embauche est obligatoire.";
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        $employeModel = new EmployeModel();
        $existing = $employeModel->where('email', $email)->first();
        if ($existing && (int) $existing['id'] !== $id) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'role' => $role,
            'departement_id' => $departementId,
            'date_embauche' => $dateEmbauche,
        ];
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (! $employeModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', "Impossible de mettre à jour l'employé.");
        }

        return redirect()->to('admin/employes')->with('success', 'Employé mis à jour.');
    }

    public function toggleEmploye(int $id)
    {
        $employeModel = new EmployeModel();
        $employe = $employeModel->find($id);
        if ($employe === null) {
            return redirect()->back()->with('error', 'Employé introuvable.');
        }

        $newStatus = ((int) $employe['actif'] === 1) ? 0 : 1;
        $employeModel->update($id, ['actif' => $newStatus]);

        $message = $newStatus === 1 ? 'Employé réactivé.' : 'Employé désactivé.';
        return redirect()->back()->with('success', $message);
    }

    public function departements()
    {
        $user = $this->getUserWithDepartement();
        $departements = (new DepartementModel())
            ->orderBy('nom', 'ASC')
            ->findAll();

        return view('admin/departements', [
            'title' => 'Gestion des départements — Admin',
            'user' => $user,
            'departements' => $departements,
        ]);
    }

    public function storeDepartement()
    {
        $nom = trim((string) ($this->request->getPost('nom') ?? ''));
        $description = trim((string) ($this->request->getPost('description') ?? ''));

        if ($nom === '') {
            return redirect()->back()->withInput()->with('error', 'Le nom du département est obligatoire.');
        }

        $model = new DepartementModel();
        if (! $model->insert(['nom' => $nom, 'description' => $description])) {
            return redirect()->back()->withInput()->with('error', "Impossible d'ajouter le département.");
        }

        return redirect()->back()->with('success', 'Département ajouté.');
    }

    public function updateDepartement(int $id)
    {
        $nom = trim((string) ($this->request->getPost('nom') ?? ''));
        $description = trim((string) ($this->request->getPost('description') ?? ''));

        if ($nom === '') {
            return redirect()->back()->withInput()->with('error', 'Le nom du département est obligatoire.');
        }

        $model = new DepartementModel();
        if (! $model->update($id, ['nom' => $nom, 'description' => $description])) {
            return redirect()->back()->withInput()->with('error', "Impossible de mettre à jour le département.");
        }

        return redirect()->back()->with('success', 'Département mis à jour.');
    }

    public function deleteDepartement(int $id)
    {
        $model = new DepartementModel();
        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Suppression impossible. Des employés sont peut-être rattachés à ce département.');
        }

        return redirect()->back()->with('success', 'Département supprimé.');
    }

    public function types()
    {
        $user = $this->getUserWithDepartement();
        $types = (new TypeCongeModel())
            ->orderBy('libelle', 'ASC')
            ->findAll();

        return view('admin/types', [
            'title' => 'Gestion des types de congé — Admin',
            'user' => $user,
            'types' => $types,
        ]);
    }

    public function storeType()
    {
        $libelle = trim((string) ($this->request->getPost('libelle') ?? ''));
        $joursAnnuel = (int) ($this->request->getPost('jours_annuels') ?? 0);
        $deductible = (int) ($this->request->getPost('deductible') ?? 0);

        if ($libelle === '' || $joursAnnuel <= 0) {
            return redirect()->back()->withInput()->with('error', 'Veuillez renseigner le libellé et le nombre de jours.');
        }

        $model = new TypeCongeModel();
        if (! $model->insert(['libelle' => $libelle, 'jours_annuels' => $joursAnnuel, 'deductible' => $deductible])) {
            return redirect()->back()->withInput()->with('error', "Impossible d'ajouter le type de congé.");
        }

        return redirect()->back()->with('success', 'Type de congé ajouté.');
    }

    public function updateType(int $id)
    {
        $libelle = trim((string) ($this->request->getPost('libelle') ?? ''));
        $joursAnnuel = (int) ($this->request->getPost('jours_annuels') ?? 0);
        $deductible = (int) ($this->request->getPost('deductible') ?? 0);

        if ($libelle === '' || $joursAnnuel <= 0) {
            return redirect()->back()->withInput()->with('error', 'Veuillez renseigner le libellé et le nombre de jours.');
        }

        $model = new TypeCongeModel();
        if (! $model->update($id, ['libelle' => $libelle, 'jours_annuels' => $joursAnnuel, 'deductible' => $deductible])) {
            return redirect()->back()->withInput()->with('error', "Impossible de mettre à jour le type de congé.");
        }

        return redirect()->back()->with('success', 'Type de congé mis à jour.');
    }

    public function deleteType(int $id)
    {
        $model = new TypeCongeModel();
        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Suppression impossible. Ce type est peut-être utilisé dans des demandes.');
        }

        return redirect()->back()->with('success', 'Type de congé supprimé.');
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
        $employes = (new EmployeModel())->orderBy('nom', 'ASC')->findAll();
        $types = (new TypeCongeModel())->orderBy('libelle', 'ASC')->findAll();

        return view('admin/soldes', [
            'title' => 'Gestion des soldes — Admin',
            'user' => $user,
            'rows' => $rows,
            'departements' => $departements,
            'departementFilter' => $departementId,
            'year' => $year,
            'years' => $years,
            'employes' => $employes,
            'types' => $types,
        ]);
    }

    public function saveSolde()
    {
        $employeId = (int) ($this->request->getPost('employe_id') ?? 0);
        $typeId = (int) ($this->request->getPost('type_conge_id') ?? 0);
        $annee = (int) ($this->request->getPost('annee') ?? 0);
        $attribues = (int) ($this->request->getPost('jours_attribues') ?? 0);
        $pris = (int) ($this->request->getPost('jours_pris') ?? 0);

        if ($employeId <= 0 || $typeId <= 0 || $annee <= 0) {
            return redirect()->back()->withInput()->with('error', 'Veuillez remplir tous les champs obligatoires.');
        }
        if ($attribues < 0 || $pris < 0 || $pris > $attribues) {
            return redirect()->back()->withInput()->with('error', 'Les valeurs de solde sont invalides.');
        }

        $soldeModel = new SoldeModel();
        $existing = $soldeModel->findByEmployeTypeYear($employeId, $typeId, $annee);
        if ($existing) {
            $soldeModel->update((int) $existing['id'], [
                'jours_attribues' => $attribues,
                'jours_pris' => $pris,
            ]);
            $message = 'Solde mis à jour.';
        } else {
            $soldeModel->insert([
                'employe_id' => $employeId,
                'type_conge_id' => $typeId,
                'annee' => $annee,
                'jours_attribues' => $attribues,
                'jours_pris' => $pris,
            ]);
            $message = 'Solde créé.';
        }

        return redirect()->back()->with('success', $message);
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
}
