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
<section id="page-dashboard-employe">
    <div class="app-wrap">
        <?= $this->include('employe/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Tableau de bord</div>
                    <div class="topbar-breadcrumb">Accueil</div>
                </div>
                <div class="topbar-actions">
                    <a href="<?= site_url('employe/conges/nouveau') ?>" class="btn-forest" style="padding:7px 14px;font-size:.82rem">
                        <i class="bi bi-plus-lg"></i> Nouvelle demande
                    </a>
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
                        <div class="metric-top"><div class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></div></div>
                        <div class="metric-val"><?= esc($stats['en_attente'] ?? 0) ?></div>
                        <div class="metric-label">En attente</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-green"><i class="bi bi-check-circle"></i></div></div>
                        <div class="metric-val"><?= esc($stats['approuvee'] ?? 0) ?></div>
                        <div class="metric-label">Approuvées</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-forest"><i class="bi bi-calendar-check"></i></div></div>
                        <div class="metric-val"><?= esc($totalRestants) ?></div>
                        <div class="metric-label">Jours restants</div>
                        <div class="metric-sub">sur <?= esc($totalAttribues) ?> cette année</div>
                    </div>
                    <div class="metric">
                        <div class="metric-top"><div class="metric-icon mi-red"><i class="bi bi-x-circle"></i></div></div>
                        <div class="metric-val"><?= esc($stats['refusee'] ?? 0) ?></div>
                        <div class="metric-label">Refusées</div>
                    </div>
                </div>

                <div class="data-card">
                    <div class="data-card-head"><h3>Mes soldes de congés — <?= esc($year) ?></h3></div>
                    <?php if (empty($soldes)): ?>
                        <div class="empty">
                            <i class="bi bi-clipboard"></i>
                            <p>Aucun solde disponible pour cette année.</p>
                        </div>
                    <?php else: ?>
                        <div style="padding:1rem 1.25rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
                            <?php foreach ($soldes as $solde): ?>
                                <?php
                                $ratio = max(0, min(100, (int) ($solde['ratio'] ?? 0)));
                                $fillClass = '';
                                if ($ratio <= 25) {
                                    $fillClass = 'danger';
                                } elseif ($ratio <= 50) {
                                    $fillClass = 'warn';
                                }
                                ?>
                                <div class="solde-card" style="margin:0">
                                    <div class="solde-header">
                                        <span class="solde-type"><?= esc($solde['libelle']) ?></span>
                                        <span class="solde-nums"><strong><?= esc($solde['jours_restants']) ?></strong> / <?= esc($solde['jours_attribues']) ?> j</span>
                                    </div>
                                    <div class="solde-bar"><div class="solde-fill <?= esc($fillClass) ?>" style="width:<?= esc($ratio) ?>%"></div></div>
                                    <div class="solde-label"><?= esc($solde['jours_restants']) ?> jours restants · <?= esc($solde['jours_pris']) ?> pris</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="data-card">
                    <div class="data-card-head">
                        <h3>Mes dernières demandes</h3>
                        <a href="<?= site_url('employe/conges') ?>" style="font-size:.8rem;color:var(--forest);text-decoration:none">Voir tout →</a>
                    </div>
                    <?php if (empty($recentConges)): ?>
                        <div class="empty">
                            <i class="bi bi-calendar3"></i>
                            <p>Aucune demande pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Type</th><th>Du</th><th>Au</th><th>Durée</th><th>Statut</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentConges as $conge): ?>
                                    <?php $statut = $conge['statut']; ?>
                                    <tr>
                                        <td><span class="type-badge <?= esc($typeClass($conge['type_libelle'])) ?>"><?= esc($conge['type_libelle']) ?></span></td>
                                        <td class="td-muted"><?= esc(date('d/m/Y', strtotime($conge['date_debut']))) ?></td>
                                        <td class="td-muted"><?= esc(date('d/m/Y', strtotime($conge['date_fin']))) ?></td>
                                        <td class="td-mono"><?= esc($conge['nb_jours']) ?> j</td>
                                        <td><span class="statut <?= esc($statusClasses[$statut] ?? 's-attente') ?>"><?= esc($statusLabels[$statut] ?? $statut) ?></span></td>
                                        <td>
                                            <?php if ($statut === 'en_attente'): ?>
                                                <form method="post" action="<?= site_url('employe/conges/' . $conge['id'] . '/annuler') ?>">
                                                    <?= csrf_field() ?>
                                                    <button class="btn-sm btn-cancel" type="submit"><i class="bi bi-x"></i> Annuler</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="td-muted" style="font-size:.75rem">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span> — Projet CodeIgniter 4</div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
