# Spécifications : Système RH Interne - Entreprise TechMada

## 1. Présentation du Projet
* [cite_start]**Contexte** : Développement d'un système RH interne pour l'entreprise TechMada[cite: 1].
* [cite_start]**Objectif** : Permettre aux employés de soumettre des demandes de congé, aux responsables RH de les valider/refuser, et aux administrateurs de superviser le système[cite: 2, 3].
* [cite_start]**Contraintes de réalisation** : Durée cible de 4 heures en binôme en utilisant le framework C14[cite: 4, 5].
* [cite_start]**Logique centrale** : Le solde de congés est calculé automatiquement et constitue le cœur métier du projet[cite: 6, 35].

---

## 2. Fonctionnalités par Rôle
[cite_start]*Note : Les éléments en **gras** sont obligatoires ; les autres sont des bonus[cite: 27].*

### Employé
* [cite_start]**Rôle attribué par défaut lors de l'inscription**[cite: 9].
* [cite_start]**Identifiant technique du rôle** : `employe`[cite: 10].
* [cite_start]**Connexion et déconnexion**[cite: 11].
* [cite_start]**Soumission de demandes de congé** (incluant type, dates et motif)[cite: 12].
* [cite_start]**Consultation de ses propres demandes et de leurs statuts**[cite: 13].
* [cite_start]**Visualisation du solde de congés restant par type**[cite: 14].
* [cite_start]Possibilité d'annuler une demande encore en attente[cite: 14].
* [cite_start]Modification du profil (nom, mot de passe)[cite: 14].

### Responsable RH
* [cite_start]**Identifiant technique du rôle** : `rh`[cite: 17].
* [cite_start]**Visualisation de toutes les demandes en attente**[cite: 18].
* [cite_start]**Approbation ou refus des demandes** avec ajout d'un commentaire optionnel[cite: 19].
* [cite_start]**Mise à jour automatique du solde lors de l'approbation**[cite: 20].
* [cite_start]Filtrage des demandes par département ou par statut[cite: 20].
* [cite_start]Consultation du solde de chaque employé[cite: 20].

### Administrateur
* [cite_start]**Identifiant technique du rôle** : `admin`[cite: 23].
* [cite_start]**Gestion complète des employés** (CRUD : créer, éditer, désactiver)[cite: 24].
* [cite_start]**Gestion des départements et des types de congé** (CRUD)[cite: 24].
* [cite_start]**Tableau de bord affichant les absences du mois en cours**[cite: 24].
* [cite_start]Initialisation ou ajustement manuel du solde annuel d'un employé[cite: 25].
* [cite_start]Consultation de l'historique complet de toutes les demandes du système[cite: 26].

---

## 3. Logique Métier et Workflow
### [cite_start]Cycle de vie d'une demande [cite: 28]
1.  [cite_start]**Soumission** par l'employé : le statut devient `en_attente`[cite: 30, 31].
2.  **Traitement RH** :
    * [cite_start]Si **Approuvée** : Le solde est déduit[cite: 29].
    * [cite_start]Si **Refusée** : Le solde reste intact[cite: 32].
* [cite_start]**Règle de calcul** : Le solde est déduit uniquement au moment de l'approbation, jamais à la soumission[cite: 33].
* [cite_start]**Récrédit** : Si une demande est annulée ou refusée après avoir été préalablement approuvée, le solde est recrédité[cite: 34].

### [cite_start]Calcul du Solde [cite: 39]
* [cite_start]**Stockage** : La table `soldes` enregistre les `jours_attribues` et les `jours_pris`[cite: 40].
* [cite_start]**Calcul dynamique** : Le restant n'est jamais stocké en base, il est calculé selon la formule : $jours\_restant = jours\_attribues - jours\_pris$[cite: 40, 41].
* **Requête d'approbation** : `UPDATE soldes SET jours_pris = jours_pris + $nb_jours WHERE employe_id = ? AND type_conge_id = ? AND annee = ?`[cite: 44, 45].
* [cite_start]**Vérification critique** : Il faut toujours vérifier que $(jours\_pris + nb\_jours\_demandes) \leq jours\_attribues$ avant d'approuver une demande[cite: 48].

---

## [cite_start]4. Structure de la Base de Données (5 Tables) [cite: 36, 37]
| Table | Champs principaux |
| :--- | :--- |
| **employes** | PK, nom, prenom, email (UNIQUE), password, role, departement (FK), date_embauche, actif (0/1) |
| **departements** | PK, nom, description |
| **types_conge** | PK, libelle, jours_annuels, deductible (0/1) |
| **soldes** | PK, employe_id (FK), type_conge_id (FK), annee, jours_attribues, jours_pris |
| **conges** | PK, employe_id (FK), type_conge_id (FK), date_debut, date_fin, nb_jours, motif, statut, commentaire_rh, created_at, traite_par (FK) |

---

## 5. Directives Techniques
* [cite_start]**Sécurité** : Utilisation de `password_hash()`, filtres de routes (`AuthFilter`), et protection CSRF obligatoire sur tous les formulaires POST[cite: 51, 52].
* [cite_start]**Architecture** : Modèles C14 par table avec validation, pattern PRG (Post-Redirect-Get), et utilisation exclusive du Query Builder (pas de SQL brut)[cite: 54, 57, 60].
* **Interface** : Layout partagé (`layout/app.php`), messages via Flashdata, et aucune utilisation de JavaScript (tout est géré côté serveur)[cite: 61].
* [cite_start]**Calcul des dates** : Calculer les jours ouvrables (hors week-ends)[cite: 63]. [cite_start]Si trop complexe, utiliser les jours calendaires[cite: 66].
* **Validations** : Bloquer les demandes si la date de fin est antérieure à la date de début ou s'il y a chevauchement avec une autre demande active[cite: 64, 65].

---

## 6. Planning de Développement (4H) [cite: 67, 68]
1.  **20 min** : Setup, BDD (migrations + seeders) et définition des routes[cite: 69].
2.  **40 min** : Authentification (login, sessions, filtres par rôle)[cite: 70].
3.  **60 min** : Espace employé (soumission, liste, consultation solde)[cite: 71].
4.  **50 min** : Espace RH (gestion des approbations et mise à jour des soldes)[cite: 75].
5.  **30 min** : Espace Administrateur (CRUD employés et dashboard)[cite: 76].
6.  **20 min** : Finitions (templates, messages flash et documentation)[cite: 77].
