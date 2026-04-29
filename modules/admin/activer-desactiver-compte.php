<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$identifiant = $_GET['identifiant'] ?? '';

if (empty($identifiant)) {
    header('Location: gestion-comptes.php?msg=error');
    exit();
}

// Empêcher la désactivation de son propre compte
if ($identifiant === $_SESSION['user']['identifiant']) {
    header('Location: gestion-comptes.php?msg=self');
    exit();
}

if (toggleActiverUtilisateur($identifiant)) {
    header('Location: gestion-comptes.php?msg=toggled');
} else {
    header('Location: gestion-comptes.php?msg=error');
}
exit();