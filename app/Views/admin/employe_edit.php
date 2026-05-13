<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'employes';
$errors = session()->getFlashdata('errors') ?? [];
?>
<section id="page-admin-employe-edit">
    <div class="app-wrap">
        <?= $this->include('admin/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Éditer un employé</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/employes') ?>">Employés</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Édition</div>
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
                    <h3><i class="bi bi-pencil" style="color:var(--forest);margin-right:6px"></i>Mettre à jour l'employé</h3>
                    <form method="post" action="<?= site_url('admin/employes/' . $employe['id']) ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid-2" style="margin-bottom:1rem">
                            <div class="f-group">
                                <label class="f-label" for="prenom">Prénom</label>
                                <input id="prenom" type="text" name="prenom" class="f-input" value="<?= esc(old('prenom') ?: $employe['prenom']) ?>"/>
                                <?php if (isset($errors['prenom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="nom">Nom</label>
                                <input id="nom" type="text" name="nom" class="f-input" value="<?= esc(old('nom') ?: $employe['nom']) ?>"/>
                                <?php if (isset($errors['nom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="email">Email</label>
                                <input id="email" type="email" name="email" class="f-input" value="<?= esc(old('email') ?: $employe['email']) ?>"/>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="password">Nouveau mot de passe</label>
                                <input id="password" type="password" name="password" class="f-input" placeholder="Laisser vide pour ne pas changer"/>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="departement_id">Département</label>
                                <select id="departement_id" name="departement_id" class="f-select">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($departements as $departement): ?>
                                        <?php $selected = (string) $departement['id'] === (string) (old('departement_id') ?: $employe['departement_id']); ?>
                                        <option value="<?= esc($departement['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= esc($departement['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['departement_id'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['departement_id']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="role">Rôle</label>
                                <select id="role" name="role" class="f-select">
                                    <?php $roleValue = old('role') ?: $employe['role']; ?>
                                    <option value="employe" <?= $roleValue === 'employe' ? 'selected' : '' ?>>Employé</option>
                                    <option value="rh" <?= $roleValue === 'rh' ? 'selected' : '' ?>>Responsable RH</option>
                                    <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                                <?php if (isset($errors['role'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['role']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="date_embauche">Date d'embauche</label>
                                <input id="date_embauche" type="date" name="date_embauche" class="f-input" value="<?= esc(old('date_embauche') ?: $employe['date_embauche']) ?>"/>
                                <?php if (isset($errors['date_embauche'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_embauche']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn-forest" type="submit"><i class="bi bi-save"></i> Enregistrer</button>
                            <a href="<?= site_url('admin/employes') ?>" class="btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
