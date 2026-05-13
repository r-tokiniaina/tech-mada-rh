<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'dashboard';
$statusLabels = [
    'en_attente' => 'en attente',
    'approuvee' => 'approuvée',
    'refusee' => 'refusée',
    'annulee' => 'annulée',
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
<section id="page-dashboard-admin">
    <div class="app-wrap">
        <?= $this->include('admin/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Vue d'ensemble</div>
                    <div class="topbar-breadcrumb">Administration</div>
                </div>
                <div class="topbar-actions">
                    <a href="<?= site_url('admin/employes') ?>" class="btn-forest" style="padding:7px 14px;font-size:.82rem"><i class="bi bi-person-plus"></i> Ajouter un employé</a>
                </div>
            </div>

            <div class="content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="flash flash-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="flash flash-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <div class="metrics">
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-forest"><i class="bi bi-people"></i></div></div>
                        <div class="metric-val"><?= esc($actifs) ?></div>
                        <div class="metric-label">Employés actifs</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></div></div>
                        <div class="metric-val"><?= esc($pending) ?></div>
                        <div class="metric-label">Demandes en attente</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-green"><i class="bi bi-calendar-check"></i></div></div>
                        <div class="metric-val"><?= esc($approvedThisMonth) ?></div>
                        <div class="metric-label">Approuvées ce mois</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-blue"><i class="bi bi-building"></i></div></div>
                        <div class="metric-val"><?= esc($departementsCount) ?></div>
                        <div class="metric-label">Départements</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-red"><i class="bi bi-person-slash"></i></div></div>
                        <div class="metric-val"><?= esc($absentsToday) ?></div>
                        <div class="metric-label">Absents aujourd'hui</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
                    <div class="data-card" style="margin:0">
                        <div class="data-card-head">
                            <h3>Demandes récentes</h3>
                            <a href="<?= site_url('admin/demandes') ?>" style="font-size:.8rem;color:var(--forest);text-decoration:none">Tout voir →</a>
                        </div>
                        <?php if (empty($recentConges)): ?>
                            <div class="empty">
                                <i class="bi bi-inbox"></i>
                                <p>Aucune demande récente.</p>
                            </div>
                        <?php else: ?>
                            <table class="tbl">
                                <thead>
                                    <tr><th>Employé</th><th>Type</th><th>Durée</th><th>Statut</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentConges as $conge): ?>
                                        <?php
                                        $prenom = $conge['prenom'] ?? '';
                                        $nom = $conge['nom'] ?? '';
                                        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                        $statut = $conge['statut'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:7px">
                                                    <div class="avatar av-green" style="width:28px;height:28px;font-size:.62rem"><?= esc($initials) ?></div>
                                                    <span class="td-name" style="font-size:.84rem"><?= esc(trim($prenom . ' ' . $nom)) ?></span>
                                                </div>
                                            </td>
                                            <td><span class="type-badge <?= esc($typeClass($conge['type_libelle'])) ?>"><?= esc($conge['type_libelle']) ?></span></td>
                                            <td class="td-mono"><?= esc($conge['nb_jours']) ?> j</td>
                                            <td><span class="statut <?= esc($statusClasses[$statut] ?? 's-attente') ?>"><?= esc($statusLabels[$statut] ?? $statut) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1rem">
                        <div class="data-card" style="margin:0">
                            <div class="data-card-head"><h3><i class="bi bi-person-slash" style="color:var(--muted);margin-right:5px"></i>Absences du mois</h3></div>
                            <?php if (empty($absencesMonth)): ?>
                                <div class="empty">
                                    <i class="bi bi-calendar"></i>
                                    <p>Aucune absence ce mois.</p>
                                </div>
                            <?php else: ?>
                                <div style="padding:.75rem 1.1rem;display:flex;flex-direction:column;gap:.6rem">
                                    <?php foreach ($absencesMonth as $absence): ?>
                                        <?php
                                        $prenom = $absence['prenom'] ?? '';
                                        $nom = $absence['nom'] ?? '';
                                        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                        $periode = date('d/m', strtotime($absence['date_debut'])) . ' → ' . date('d/m', strtotime($absence['date_fin']));
                                        ?>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <div class="avatar av-amber" style="width:30px;height:30px;font-size:.65rem"><?= esc($initials) ?></div>
                                            <div>
                                                <div style="font-size:.83rem;font-weight:500;color:var(--ink)"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
                                                <div style="font-size:.72rem;color:var(--muted)"><?= esc($absence['type_libelle']) ?> · <?= esc($periode) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
