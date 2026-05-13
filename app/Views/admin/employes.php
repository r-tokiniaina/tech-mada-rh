<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'employes';
$errors = session()->getFlashdata('errors') ?? [];
$roleOld = old('role') ?: 'employe';
?>
<section id="page-admin-employes">
    <div class="app-wrap">
        <?= $this->include('admin/partials/sidebar') ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Gestion des employés</div>
                    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Employés</div>
                </div>
                <div class="topbar-actions">
                    <a href="#form-employe" class="btn-forest" style="padding:7px 14px;font-size:.82rem"><i class="bi bi-person-plus"></i> Ajouter</a>
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

                <div id="form-employe" class="form-section">
                    <h3><i class="bi bi-person-plus" style="color:var(--forest);margin-right:6px"></i>Ajouter un employé</h3>
                    <form method="post" action="<?= site_url('admin/employes') ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid-2" style="margin-bottom:1rem">
                            <div class="f-group">
                                <label class="f-label" for="prenom">Prénom</label>
                                <input id="prenom" type="text" name="prenom" class="f-input" value="<?= esc(old('prenom')) ?>"/>
                                <?php if (isset($errors['prenom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['prenom']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="nom">Nom</label>
                                <input id="nom" type="text" name="nom" class="f-input" value="<?= esc(old('nom')) ?>"/>
                                <?php if (isset($errors['nom'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="email">Email</label>
                                <input id="email" type="email" name="email" class="f-input" value="<?= esc(old('email')) ?>"/>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="password">Mot de passe initial</label>
                                <input id="password" type="password" name="password" class="f-input" placeholder="À communiquer à l'employé"/>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="departement_id">Département</label>
                                <select id="departement_id" name="departement_id" class="f-select">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($departements as $departement): ?>
                                        <option value="<?= esc($departement['id']) ?>" <?= (string) $departement['id'] === (string) old('departement_id') ? 'selected' : '' ?>><?= esc($departement['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['departement_id'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['departement_id']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="role">Rôle</label>
                                <select id="role" name="role" class="f-select">
                                    <option value="employe" <?= $roleOld === 'employe' ? 'selected' : '' ?>>Employé</option>
                                    <option value="rh" <?= $roleOld === 'rh' ? 'selected' : '' ?>>Responsable RH</option>
                                    <option value="admin" <?= $roleOld === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                                <?php if (isset($errors['role'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['role']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="f-group">
                                <label class="f-label" for="date_embauche">Date d'embauche</label>
                                <input id="date_embauche" type="date" name="date_embauche" class="f-input" value="<?= esc(old('date_embauche')) ?>"/>
                                <?php if (isset($errors['date_embauche'])): ?>
                                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_embauche']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flash flash-info" style="margin-bottom:1rem">
                            <i class="bi bi-info-circle-fill"></i>
                            <span style="font-size:.82rem">Les soldes de congés sont initialisés automatiquement selon les types configurés.</span>
                        </div>
                        <div class="form-actions">
                            <button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Créer l'employé</button>
                            <a href="<?= site_url('admin/employes') ?>" class="btn-secondary">Réinitialiser</a>
                        </div>
                    </form>
                </div>

                <div class="data-card">
                    <div class="data-card-head">
                        <h3>Tous les employés</h3>
                        <form method="get" action="<?= site_url('admin/employes') ?>" style="display:flex;gap:6px">
                            <input type="text" name="q" class="f-input" placeholder="Rechercher..." style="width:200px;padding:6px 10px;font-size:.8rem" value="<?= esc($search) ?>"/>
                            <select name="departement_id" class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                                <option value="">Tous les depts</option>
                                <?php foreach ($departements as $departement): ?>
                                    <option value="<?= esc($departement['id']) ?>" <?= (string) $departementFilter === (string) $departement['id'] ? 'selected' : '' ?>><?= esc($departement['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-secondary" type="submit" style="padding:6px 10px;font-size:.8rem">Filtrer</button>
                        </form>
                    </div>
                    <?php if (empty($employes)): ?>
                        <div class="empty">
                            <i class="bi bi-people"></i>
                            <p>Aucun employé trouvé.</p>
                        </div>
                    <?php else: ?>
                        <table class="tbl">
                            <thead>
                                <tr><th>Employé</th><th>Département</th><th>Rôle</th><th>Embauche</th><th>Statut</th><th>Solde annuel (<?= esc($currentYear) ?>)</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employes as $employe): ?>
                                    <?php
                                    $prenom = $employe['prenom'] ?? '';
                                    $nom = $employe['nom'] ?? '';
                                    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                    $actif = (int) $employe['actif'] === 1;
                                    $total = $totals[$employe['id']] ?? null;
                                    $attribues = $total['attribues'] ?? null;
                                    $pris = $total['pris'] ?? null;
                                    ?>
                                    <tr style="<?= $actif ? '' : 'opacity:.5' ?>">
                                        <td>
                                            <div class="profile-row">
                                                <div class="avatar av-green" style="width:32px;height:32px;font-size:.68rem"><?= esc($initials) ?></div>
                                                <div class="profile-info"><div class="pname"><?= esc(trim($prenom . ' ' . $nom)) ?></div><div class="pdept"><?= esc($employe['email']) ?></div></div>
                                            </div>
                                        </td>
                                        <td class="td-muted"><?= esc($employe['departement_nom'] ?? '—') ?></td>
                                        <td><span class="type-badge" style="background:#f1efe8;color:#444441"><?= esc($employe['role']) ?></span></td>
                                        <td class="td-muted td-mono" style="font-size:.78rem"><?= esc($employe['date_embauche']) ?></td>
                                        <td><span class="statut <?= $actif ? 's-approuvee' : 's-annulee' ?>" style="font-size:.68rem"><?= $actif ? 'actif' : 'inactif' ?></span></td>
                                        <td>
                                            <?php if ($attribues === null): ?>
                                                <span style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--muted)">—</span>
                                            <?php else: ?>
                                                <?php $restant = (int) $attribues - (int) $pris; ?>
                                                <span style="font-family:'DM Mono',monospace;font-size:.82rem;color:var(--forest)"><?= esc($restant) ?> / <?= esc($attribues) ?> j</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="<?= site_url('admin/employes/' . $employe['id'] . '/edit') ?>" class="btn-sm btn-edit"><i class="bi bi-pencil"></i> Éditer</a>
                                                <form method="post" action="<?= site_url('admin/employes/' . $employe['id'] . '/toggle') ?>">
                                                    <?= csrf_field() ?>
                                                    <?php if ($actif): ?>
                                                        <button class="btn-sm btn-del" type="submit"><i class="bi bi-slash-circle"></i></button>
                                                    <?php else: ?>
                                                        <button class="btn-sm btn-view" type="submit"><i class="bi bi-arrow-counterclockwise"></i> Réactiver</button>
                                                    <?php endif; ?>
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
