<?php
// modules/administration/supprimer-compte.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ../../index.php');
    exit();
}

$identifiant = $_GET['identifiant'] ?? '';
if (empty($identifiant)) {
    header('Location: gestion-comptes.php?msg=Identifiant+manquant');
    exit();
}

// Vérifier que l'utilisateur n'est pas un Super Admin (sécurité)
$user = trouverUtilisateurParIdentifiant($identifiant);
if (!$user || $user['role'] === ROLE_SUPER_ADMIN) {
    header('Location: gestion-comptes.php?msg=Suppression+non+autorisée');
    exit();
}

if (supprimerUtilisateur($identifiant)) {
    header('Location: gestion-comptes.php?msg=Compte+supprimé');
} else {
    header('Location: gestion-comptes.php?msg=Erreur+suppression');
}
exit();