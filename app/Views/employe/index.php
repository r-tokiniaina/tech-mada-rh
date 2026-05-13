<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'conges';
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
<section id="page-mes-conges">
    <div class="app-wrap">
        <?= view('employe/partials/sidebar', ['active' => $active, 'user' => $user, 'pendingCount' => $pendingCount ?? 0]) ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Mes demandes de congé</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Mes demandes</div>
                </div>
                <div class="topbar-actions">
                    <a href="<?= site_url('employe/conges/nouveau') ?>" class="btn-forest" style="padding:7px 14px;font-size:.82rem"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
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

                <div class="data-card">
                    <div class="data-card-head">
                        <h3>Toutes mes demandes</h3>
                        <form method="get" action="<?= site_url('employe/conges') ?>" style="display:flex;gap:6px">
                            <select name="statut" class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente" <?= $statutFilter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="approuvee" <?= $statutFilter === 'approuvee' ? 'selected' : '' ?>>Approuvée</option>
                                <option value="refusee" <?= $statutFilter === 'refusee' ? 'selected' : '' ?>>Refusée</option>
                                <option value="annulee" <?= $statutFilter === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                            </select>
                            <button class="btn-secondary" type="submit" style="padding:6px 10px;font-size:.8rem">Filtrer</button>
                        </form>
                    </div>
                    <?php if (empty($conges)): ?>
                        <div class="empty">
                            <i class="bi bi-calendar3"></i>
                            <p>Aucune demande trouvée.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Type</th><th>Début</th><th>Fin</th><th>Durée</th><th>Statut</th><th>Commentaire RH</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conges as $conge): ?>
                                    <?php $statut = $conge['statut']; ?>
                                    <tr>
                                        <td><span class="type-badge <?= esc($typeClass($conge['type_libelle'])) ?>"><?= esc($conge['type_libelle']) ?></span></td>
                                        <td class="td-muted"><?= esc(date('d/m/Y', strtotime($conge['date_debut']))) ?></td>
                                        <td class="td-muted"><?= esc(date('d/m/Y', strtotime($conge['date_fin']))) ?></td>
                                        <td class="td-mono"><?= esc($conge['nb_jours']) ?> j</td>
                                        <td><span class="statut <?= esc($statusClasses[$statut] ?? 's-attente') ?>"><?= esc($statusLabels[$statut] ?? $statut) ?></span></td>
                                        <td class="td-muted" style="font-size:.78rem">
                                            <?= $conge['commentaire_rh'] ? esc($conge['commentaire_rh']) : '—' ?>
                                        </td>
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
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
