<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'profil';
$errors = session()->getFlashdata('errors') ?? [];
$prenom = $user['prenom'] ?? '';
$nom = $user['nom'] ?? '';
$initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
?>
<section id="page-profil-employe">
    <div class="app-wrap">
        <?= $this->include('employe/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Mon profil</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Profil</div>
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
                    <div class="profile-row">
                        <div class="avatar av-green"><?= esc($initials) ?></div>
                        <div class="profile-info">
                            <div class="pname"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
                            <div class="pdept">Employé · <?= esc($user['departement_nom'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="inline-stats">
                        <div class="inline-stat"><strong>Email</strong> <?= esc($user['email'] ?? '') ?></div>
                        <div class="inline-stat"><strong>Embauche</strong> <?= esc($user['date_embauche'] ?? '—') ?></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Mettre à jour mes informations</h3>
                    <form method="post" action="<?= site_url('employe/profil') ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid-2">
                            <div class="f-group">
                                <label class="f-label" for="nom">Nom</label>
                                <input id="nom" type="text" name="nom" class="f-input" value="<?= esc(old('nom') ?: ($user['nom'] ?? '')) ?>" required/>
                                <?php if (isset($errors['nom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="prenom">Prénom</label>
                                <input id="prenom" type="text" name="prenom" class="f-input" value="<?= esc(old('prenom') ?: ($user['prenom'] ?? '')) ?>" required/>
                                <?php if (isset($errors['prenom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-grid-2" style="margin-top:1rem">
                            <div class="f-group">
                                <label class="f-label" for="password">Nouveau mot de passe</label>
                                <input id="password" type="password" name="password" class="f-input" placeholder="Laisser vide pour ne pas changer"/>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="password_confirm">Confirmer le mot de passe</label>
                                <input id="password_confirm" type="password" name="password_confirm" class="f-input" placeholder="Répéter le mot de passe"/>
                                <?php if (isset($errors['password_confirm'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password_confirm']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn-forest" type="submit"><i class="bi bi-save"></i> Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
