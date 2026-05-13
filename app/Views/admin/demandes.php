<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'demandes';
$statusLabels = [
    '' => 'Tous les statuts',
    'en_attente' => 'En attente',
    'approuvee' => 'Approuvée',
    'refusee' => 'Refusée',
    'annulee' => 'Annulée',
];
$statusClasses = [
    'en_attente' => 's-attente',
    'approuvee' => 's-approuvee',
    'refusee' => 's-refusee',
    'annulee' => 's-annulee',
];
$typeClass = function (string $libelle): string {
    $lower = strtolower($libelle);
    if (str_contains($lower, 'annuel')) {
        return 't-annuel';
    }
    if (str_contains($lower, 'maladie')) {
        return 't-maladie';
    }
    if (str_contains($lower, 'sans solde')) {
        return 't-sans-solde';
    }
    return 't-special';
};
?>
<section id="page-admin-demandes">
    <div class="app-wrap">
        <?= view('admin/partials/sidebar', ['active' => $active, 'user' => $user]) ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Historique des demandes</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Demandes</div>
                </div>
            </div>

            <div class="content">
                <form method="get" action="<?= site_url('admin/demandes') ?>" style="display:flex;gap:8px;margin-bottom:1.25rem;flex-wrap:wrap">
                    <select name="statut" class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                        <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= (string) $statutFilter === (string) $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="departement_id" class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                        <option value="">Tous les départements</option>
                        <?php foreach ($departements as $departement): ?>
                            <option value="<?= esc($departement['id']) ?>" <?= (string) $departementFilter === (string) $departement['id'] ? 'selected' : '' ?>><?= esc($departement['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-secondary" type="submit" style="padding:6px 12px;font-size:.8rem">Filtrer</button>
                </form>

                <div class="data-card">
                    <div class="data-card-head"><h3>Toutes les demandes</h3></div>
                    <?php if (empty($conges)): ?>
                        <div class="empty">
                            <i class="bi bi-inbox"></i>
                            <p>Aucune demande trouvée.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Employé</th><th>Département</th><th>Type</th><th>Période</th><th>Durée</th><th>Statut</th><th>Traité par</th><th>Commentaire RH</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conges as $conge): ?>
                                    <?php
                                    $prenom = $conge['prenom'] ?? '';
                                    $nom = $conge['nom'] ?? '';
                                    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                    $statut = $conge['statut'];
                                    $traite = trim(($conge['traite_prenom'] ?? '') . ' ' . ($conge['traite_nom'] ?? ''));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="profile-row">
                                                <div class="avatar av-blue" style="width:32px;height:32px;font-size:.7rem"><?= esc($initials) ?></div>
                                                <div class="profile-info">
                                                    <div class="pname"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
                                                    <div class="pdept"><?= esc($conge['departement_nom'] ?? '—') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="td-muted"><?= esc($conge['departement_nom'] ?? '—') ?></td>
                                        <td><span class="type-badge <?= esc($typeClass($conge['type_libelle'])) ?>"><?= esc($conge['type_libelle']) ?></span></td>
                                        <td class="td-muted" style="font-size:.8rem"><?= esc(date('d/m/Y', strtotime($conge['date_debut']))) ?> – <?= esc(date('d/m/Y', strtotime($conge['date_fin']))) ?></td>
                                        <td class="td-mono"><?= esc($conge['nb_jours']) ?> j</td>
                                        <td><span class="statut <?= esc($statusClasses[$statut] ?? 's-attente') ?>"><?= esc($statusLabels[$statut] ?? $statut) ?></span></td>
                                        <td class="td-muted" style="font-size:.78rem"><?= $traite !== '' ? esc($traite) : '—' ?></td>
                                        <td class="td-muted" style="font-size:.78rem"><?= $conge['commentaire_rh'] ? esc($conge['commentaire_rh']) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
