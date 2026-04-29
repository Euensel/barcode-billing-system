<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-produits.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$code_barre = $_GET['code_barre'] ?? '';
if ($code_barre && supprimerProduit($code_barre)) {
    header('Location: ' . BASE_URL . '/modules/produits/liste.php?msg=supprime');
} else {
    header('Location: ' . BASE_URL . '/modules/produits/liste.php?msg=erreur');
}
exit();