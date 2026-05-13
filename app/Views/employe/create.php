<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<?php
$active = 'create';
$errors = session()->getFlashdata('errors') ?? [];
$selectedType = old('type_conge_id');
?>
<section id="page-form-conge">
    <div class="app-wrap">
        <?= view('employe/partials/sidebar', ['active' => $active, 'user' => $user, 'pendingCount' => $pendingCount ?? 0]) ?>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Nouvelle demande de congé</div>
                    <div class="topbar-breadcrumb">
                        <a href="<?= site_url('employe/dashboard') ?>">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Nouvelle demande
                    </div>
                </div>
            </div>

            <div class="content">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="flash flash-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start" class="form-layout">
                    <div>
                        <div class="form-section">
                            <h3>Détails de la demande</h3>

                            <form method="post" action="<?= site_url('employe/conges') ?>">
                                <?= csrf_field() ?>
                                <div class="f-group" style="margin-bottom:1rem">
                                    <label class="f-label" for="type_conge_id">Type de congé <span style="color:var(--danger)">*</span></label>
                                    <select id="type_conge_id" name="type_conge_id" class="f-select" required>
                                        <option value="">-- Choisir un type --</option>
                                        <?php foreach ($types as $type): ?>
                                            <?php
                                            $solde = $soldesByType[$type['id']] ?? null;
                                            $restant = $solde ? ((int) $solde['jours_attribues'] - (int) $solde['jours_pris']) : null;
                                            $label = $type['libelle'];
                                            if ($restant !== null) {
                                                $label .= ' (' . $restant . ' j restants)';
                                            }
                                            ?>
                                            <option value="<?= esc($type['id']) ?>" <?= (string) $type['id'] === (string) $selectedType ? 'selected' : '' ?>><?= esc($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['type_conge_id'])): ?>
                                        <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['type_conge_id']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-grid-2" style="margin-bottom:1rem">
                                    <div class="f-group">
                                        <label class="f-label" for="date_debut">Date de début <span style="color:var(--danger)">*</span></label>
                                        <input id="date_debut" type="date" name="date_debut" class="f-input" value="<?= esc(old('date_debut')) ?>" required/>
                                        <?php if (isset($errors['date_debut'])): ?>
                                            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_debut']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="f-group">
                                        <label class="f-label" for="date_fin">Date de fin <span style="color:var(--danger)">*</span></label>
                                        <input id="date_fin" type="date" name="date_fin" class="f-input" value="<?= esc(old('date_fin')) ?>" required/>
                                        <?php if (isset($errors['date_fin'])): ?>
                                            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_fin']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($computedDays !== null): ?>
                                    <div class="f-computed">
                                        <div class="f-computed-num"><?= esc($computedDays) ?></div>
                                        <div class="f-computed-label">jours ouvrables calculés<br><span style="font-size:.7rem;opacity:.7"><?= esc($rangeLabel) ?></span></div>
                                    </div>
                                <?php endif; ?>

                                <div class="f-group" style="margin-bottom:1rem">
                                    <label class="f-label" for="motif">Motif (optionnel)</label>
                                    <textarea id="motif" name="motif" class="f-textarea" placeholder="Précisez le motif de votre demande si nécessaire..." rows="3"><?= esc(old('motif')) ?></textarea>
                                    <div class="f-hint">Le motif est visible par le responsable RH.</div>
                                </div>

                                <div class="form-actions">
                                    <button class="btn-forest" type="submit"><i class="bi bi-send"></i> Soumettre la demande</button>
                                    <a href="<?= site_url('employe/dashboard') ?>" class="btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1rem">
                        <div class="data-card" style="margin:0">
                            <div class="data-card-head"><h3><i class="bi bi-piggy-bank" style="color:var(--forest);margin-right:5px"></i>Vos soldes actuels</h3></div>
                            <?php if (empty($soldes)): ?>
                                <div class="empty">
                                    <i class="bi bi-clipboard"></i>
                                    <p>Aucun solde disponible pour cette année.</p>
                                </div>
                            <?php else: ?>
                                <div style="padding:.75rem 1.1rem;display:flex;flex-direction:column;gap:.75rem">
                                    <?php foreach ($soldes as $solde): ?>
                                        <?php $restant = (int) $solde['jours_attribues'] - (int) $solde['jours_pris']; ?>
                                        <div>
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                                <span style="font-size:.8rem;color:var(--ink)"><?= esc($solde['libelle']) ?></span>
                                                <span style="font-family:'DM Mono',monospace;font-size:.8rem;color:var(--forest);font-weight:500"><?= esc($restant) ?> j</span>
                                            </div>
                                            <?php $ratio = $solde['jours_attribues'] > 0 ? (int) round(($restant / $solde['jours_attribues']) * 100) : 0; ?>
                                            <div class="solde-bar"><div class="solde-fill" style="width:<?= esc(max(0, min(100, $ratio))) ?>%"></div></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flash flash-info" style="margin:0">
                            <i class="bi bi-info-circle-fill"></i>
                            <span style="font-size:.8rem">Le solde est déduit uniquement à l'approbation de votre responsable.</span>
                        </div>
                        <div style="background:var(--cream);border:1px solid var(--border);border-radius:8px;padding:.85rem 1rem">
                            <div style="font-size:.78rem;font-weight:500;color:var(--ink);margin-bottom:.5rem"><i class="bi bi-clipboard-check" style="color:var(--forest);margin-right:5px"></i>Rappel des règles</div>
                            <ul style="margin:0;padding-left:1rem;font-size:.75rem;color:var(--muted);line-height:1.7">
                                <li>Préavis minimum : 48h avant la date de début</li>
                                <li>Pas de chevauchement avec une demande en cours</li>
                                <li>Solde insuffisant = demande refusée automatiquement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-app"><i class="bi bi-c-circle"></i> <?= esc(date('Y')) ?> <span>TechMada RH</span></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
