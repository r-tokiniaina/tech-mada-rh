<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'types';
$deductibleOld = old('deductible');
if ($deductibleOld === '') {
    $deductibleOld = '1';
}
?>
<section id="page-admin-types">
    <div class="app-wrap">
        <?= $this->include('admin/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Gestion des types de congé</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Types de congé</div>
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

                <div class="form-section">
                    <h3><i class="bi bi-tags" style="color:var(--forest);margin-right:6px"></i>Ajouter un type de congé</h3>
                    <form method="post" action="<?= site_url('admin/types') ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid-2">
                            <div class="f-group">
                                <label class="f-label" for="libelle">Libellé</label>
                                <input id="libelle" type="text" name="libelle" class="f-input" value="<?= esc(old('libelle')) ?>"/>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="jours_annuels">Jours annuels</label>
                                <input id="jours_annuels" type="number" name="jours_annuels" class="f-input" value="<?= esc(old('jours_annuels')) ?>"/>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="deductible">Déductible du solde ?</label>
                                <select id="deductible" name="deductible" class="f-select">
                                    <option value="1" <?= $deductibleOld === '1' ? 'selected' : '' ?>>Oui</option>
                                    <option value="0" <?= $deductibleOld === '0' ? 'selected' : '' ?>>Non</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Ajouter</button>
                        </div>
                    </form>
                </div>

                <div class="data-card">
                    <div class="data-card-head"><h3>Types de congé</h3></div>
                    <?php if (empty($types)): ?>
                        <div class="empty">
                            <i class="bi bi-tags"></i>
                            <p>Aucun type de congé créé.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Libellé</th><th>Jours annuels</th><th>Déductible</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($types as $type): ?>
                                    <tr>
                                        <td><?= esc($type['libelle']) ?></td>
                                        <td class="td-mono"><?= esc($type['jours_annuels']) ?> j</td>
                                        <td><?= (int) $type['deductible'] === 1 ? 'Oui' : 'Non' ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <form method="post" action="<?= site_url('admin/types/' . $type['id']) ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="text" name="libelle" class="f-input" placeholder="Libellé" value="<?= esc($type['libelle']) ?>" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px"/>
                                                    <input type="number" name="jours_annuels" class="f-input" placeholder="Jours" value="<?= esc($type['jours_annuels']) ?>" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px"/>
                                                    <select name="deductible" class="f-select" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px">
                                                        <option value="1" <?= (int) $type['deductible'] === 1 ? 'selected' : '' ?>>Oui</option>
                                                        <option value="0" <?= (int) $type['deductible'] === 0 ? 'selected' : '' ?>>Non</option>
                                                    </select>
                                                    <button class="btn-sm btn-edit" type="submit"><i class="bi bi-pencil"></i> Mettre à jour</button>
                                                </form>
                                                <form method="post" action="<?= site_url('admin/types/' . $type['id'] . '/delete') ?>">
                                                    <?= csrf_field() ?>
                                                    <button class="btn-sm btn-del" type="submit"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
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
