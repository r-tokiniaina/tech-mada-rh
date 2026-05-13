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
$statusDisplay = [
    'en_attente' => 'en attente',
    'approuvee' => 'approuvée',
    'refusee' => 'refusée',
    'annulee' => 'annulée',
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
$pending = $stats['en_attente'] ?? 0;
?>
<section id="page-liste-rh">
    <div class="app-wrap">
        <?= $this->include('rh/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Demandes à traiter</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('rh/demandes') ?>">Accueil</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Demandes</div>
                </div>
                <div class="topbar-actions">
                    <span style="font-size:.8rem;color:var(--muted);background:var(--warn-bg);border:1px solid var(--warn-br);border-radius:6px;padding:5px 10px;display:flex;align-items:center;gap:5px;color:var(--warn)">
                        <i class="bi bi-hourglass-split"></i> <?= esc($pending) ?> en attente
                    </span>
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

                <form method="get" action="<?= site_url('rh/demandes') ?>" style="display:flex;gap:8px;margin-bottom:1.25rem;flex-wrap:wrap">
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
                                <tr><th>Employé</th><th>Type</th><th>Période</th><th>Durée</th><th>Solde dispo</th><th>Statut</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conges as $conge): ?>
                                    <?php
                                    $prenom = $conge['prenom'] ?? '';
                                    $nom = $conge['nom'] ?? '';
                                    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                    $statut = $conge['statut'];
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
                                        <td><span class="type-badge <?= esc($typeClass($conge['type_libelle'])) ?>"><?= esc($conge['type_libelle']) ?></span></td>
                                        <td class="td-muted" style="font-size:.8rem"><?= esc(date('d/m/Y', strtotime($conge['date_debut']))) ?> – <?= esc(date('d/m/Y', strtotime($conge['date_fin']))) ?></td>
                                        <td class="td-mono"><?= esc($conge['nb_jours']) ?> j</td>
                                        <td>
                                            <?php if ((int) $conge['deductible'] !== 1): ?>
                                                <span class="td-muted" style="font-size:.78rem">—</span>
                                            <?php elseif ($conge['solde_restant'] === null): ?>
                                                <span style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--danger);font-weight:500">n/a</span>
                                            <?php else: ?>
                                                <?php if ($conge['solde_insuffisant']): ?>
                                                    <span style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--warn);font-weight:500"><?= esc($conge['solde_restant']) ?> j</span>
                                                    <span style="font-size:.72rem;color:var(--danger)"> ⚠ insuffisant</span>
                                                <?php else: ?>
                                                    <span style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--success);font-weight:500"><?= esc($conge['solde_restant']) ?> j</span>
                                                    <span style="font-size:.72rem;color:var(--muted)"> dispo</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="statut <?= esc($statusClasses[$statut] ?? 's-attente') ?>"><?= esc($statusDisplay[$statut] ?? $statut) ?></span></td>
                                        <td>
                                            <?php if ($statut === 'en_attente'): ?>
                                                <form method="post" action="<?= site_url('rh/conges/' . $conge['id'] . '/decision') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="text" name="commentaire" class="f-input" placeholder="Commentaire (optionnel)" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px"/>
                                                    <div class="action-btns">
                                                        <?php if ($conge['can_approve']): ?>
                                                            <button name="decision" value="approuver" class="btn-sm btn-approve" type="submit"><i class="bi bi-check-lg"></i> Approuver</button>
                                                        <?php else: ?>
                                                            <button class="btn-sm btn-approve" type="submit" disabled style="opacity:.4;cursor:not-allowed"><i class="bi bi-check-lg"></i> Approuver</button>
                                                        <?php endif; ?>
                                                        <button name="decision" value="refuser" class="btn-sm btn-refuse" type="submit"><i class="bi bi-x-lg"></i> Refuser</button>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <span class="td-muted" style="font-size:.75rem">
                                                    <?= $conge['commentaire_rh'] ? esc($conge['commentaire_rh']) : '—' ?>
                                                </span>
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
