<?php
$active = $active ?? '';
$prenom = $user['prenom'] ?? '';
$nom = $user['nom'] ?? '';
$initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo-icon" style="background:var(--ink);border:1px solid rgba(255,255,255,.15)"><i class="bi bi-shield-check" style="color:var(--leaf)"></i></div>
        <div class="sidebar-brand-name">TechMada RH
            <span>Administration</span>
        </div>
    </div>
    <div class="sidebar-section">Gestion</div>
    <ul class="sidebar-nav">
        <li><a href="<?= site_url('admin/dashboard') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
        <li><a href="<?= site_url('admin/demandes') ?>" class="<?= $active === 'demandes' ? 'active' : '' ?>"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
        <li><a href="<?= site_url('admin/employes') ?>" class="<?= $active === 'employes' ? 'active' : '' ?>"><i class="bi bi-people"></i> Employés</a></li>
        <li><a href="<?= site_url('admin/departements') ?>" class="<?= $active === 'departements' ? 'active' : '' ?>"><i class="bi bi-building"></i> Départements</a></li>
        <li><a href="<?= site_url('admin/types') ?>" class="<?= $active === 'types' ? 'active' : '' ?>"><i class="bi bi-tags"></i> Types de congé</a></li>
        <li><a href="<?= site_url('admin/soldes') ?>" class="<?= $active === 'soldes' ? 'active' : '' ?>"><i class="bi bi-sliders"></i> Soldes annuels</a></li>
    </ul>
    <div class="sidebar-user">
        <div class="s-user-row">
            <div class="avatar" style="background:#5a2d82;width:32px;height:32px;font-size:.7rem"><?= esc($initials) ?></div>
            <div>
                <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
                <div class="user-role">Admin système</div>
            </div>
            <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Déconnexion"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</aside>
