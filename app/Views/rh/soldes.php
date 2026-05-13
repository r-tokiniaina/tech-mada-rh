<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'soldes';
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
<section id="page-soldes-rh">
    <div class="app-wrap">
        <?= $this->include('rh/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Soldes des employés</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('rh/demandes') ?>">Accueil</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Soldes</div>
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

                <form method="get" action="<?= site_url('rh/soldes') ?>" style="display:flex;gap:8px;margin-bottom:1.25rem;flex-wrap:wrap">
                    <select name="annee" class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                        <?php if (empty($years)): ?>
                            <option value="<?= esc($year) ?>"><?= esc($year) ?></option>
                        <?php else: ?>
                            <?php foreach ($years as $availableYear): ?>
                                <option value="<?= esc($availableYear) ?>" <?= (int) $year === (int) $availableYear ? 'selected' : '' ?>><?= esc($availableYear) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    <div class="data-card-head"><h3>Soldes — <?= esc($year) ?></h3></div>
                    <?php if (empty($rows)): ?>
                        <div class="empty">
                            <i class="bi bi-people"></i>
                            <p>Aucun solde trouvé pour cette sélection.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Employé</th><th>Département</th><th>Type</th><th>Attribués</th><th>Pris</th><th>Restants</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <?php
                                    $prenom = $row['prenom'] ?? '';
                                    $nom = $row['nom'] ?? '';
                                    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                    $restant = (int) $row['jours_attribues'] - (int) $row['jours_pris'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="profile-row">
                                                <div class="avatar av-blue" style="width:32px;height:32px;font-size:.7rem"><?= esc($initials) ?></div>
                                                <div class="profile-info">
                                                    <div class="pname"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
                                                    <div class="pdept"><?= esc($row['email'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="td-muted"><?= esc($row['departement_nom'] ?? '—') ?></td>
                                        <td><span class="type-badge <?= esc($typeClass($row['type_libelle'])) ?>"><?= esc($row['type_libelle']) ?></span></td>
                                        <td class="td-mono"><?= esc($row['jours_attribues']) ?> j</td>
                                        <td class="td-mono"><?= esc($row['jours_pris']) ?> j</td>
                                        <td class="td-mono"><?= esc($restant) ?> j</td>
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
