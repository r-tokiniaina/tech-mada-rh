<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'departements';
?>
<section id="page-admin-departements">
    <div class="app-wrap">
        <?= view('admin/partials/sidebar', ['active' => $active, 'user' => $user]) ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Gestion des départements</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Départements</div>
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
                    <h3><i class="bi bi-building" style="color:var(--forest);margin-right:6px"></i>Ajouter un département</h3>
                    <form method="post" action="<?= site_url('admin/departements') ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid-2">
                            <div class="f-group">
                                <label class="f-label" for="nom">Nom</label>
                                <input id="nom" type="text" name="nom" class="f-input" value="<?= esc(old('nom')) ?>"/>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="description">Description</label>
                                <input id="description" type="text" name="description" class="f-input" value="<?= esc(old('description')) ?>"/>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Ajouter</button>
                        </div>
                    </form>
                </div>

                <div class="data-card">
                    <div class="data-card-head"><h3>Départements</h3></div>
                    <?php if (empty($departements)): ?>
                        <div class="empty">
                            <i class="bi bi-building"></i>
                            <p>Aucun département créé.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Nom</th><th>Description</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departements as $departement): ?>
                                    <tr>
                                        <td><?= esc($departement['nom']) ?></td>
                                        <td class="td-muted"><?= esc($departement['description']) ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <form method="post" action="<?= site_url('admin/departements/' . $departement['id']) ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="text" name="nom" class="f-input" placeholder="Nom" value="<?= esc($departement['nom']) ?>" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px"/>
                                                    <input type="text" name="description" class="f-input" placeholder="Description" value="<?= esc($departement['description']) ?>" style="padding:6px 10px;font-size:.75rem;margin-bottom:6px"/>
                                                    <button class="btn-sm btn-edit" type="submit"><i class="bi bi-pencil"></i> Mettre à jour</button>
                                                </form>
                                                <form method="post" action="<?= site_url('admin/departements/' . $departement['id'] . '/delete') ?>">
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
