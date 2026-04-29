<?php

require_once __DIR__ . '/../config/config.php';

$user_role = $_SESSION['user']['role'] ?? null;
$user_nom = $_SESSION['user']['nom_complet'] ?? 'Utilisateur';

$current_page = basename($_SERVER['REQUEST_URI']);
if (strpos($current_page, '?') !== false) {
    $current_page = substr($current_page, 0, strpos($current_page, '?'));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de facturation</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<header class="main-header">
    <div class="container-header">
        <div class="logo">
            <a href="<?= BASE_URL ?>/index.php">
                <i class='bx bx-receipt'></i>
                <span>FactuPro</span>
            </a>
        </div>
        
        <!-- Bouton menu burger (visible sur mobile) -->
        <div class="menu-toggle" id="menu-toggle">
            <i class='bx bx-menu'></i>
        </div>
        
        <!-- Navigation -->
        <nav class="main-nav" id="main-nav">
            <ul>
                <?php if (in_array($user_role, [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])): ?>
                    <li><a href="<?= BASE_URL ?>/modules/facturation/nouvelle-facture.php" class="<?= $current_page == 'nouvelle-facture.php' ? 'active' : '' ?>"><i class='bx bx-plus-circle'></i> Nouvelle facture</a></li>
                    <li><a href="<?= BASE_URL ?>/modules/facturation/mes-factures.php" class="<?= $current_page == 'mes-factures.php' ? 'active' : '' ?>"><i class='bx bx-list-ul'></i> Mes factures</a></li>
                <?php endif; ?>
                <?php if (in_array($user_role, [ROLE_MANAGER, ROLE_SUPER_ADMIN])): ?>
                    <li><a href="<?= BASE_URL ?>/modules/produits/enregistrer.php" class="<?= $current_page == 'enregistrer.php' ? 'active' : '' ?>"><i class='bx bx-barcode-reader'></i> Enregistrer produit</a></li>
                    <li><a href="<?= BASE_URL ?>/modules/produits/liste.php" class="<?= $current_page == 'liste.php' ? 'active' : '' ?>"><i class='bx bx-list-ul'></i> Liste produits</a></li>
                    <li><a href="<?= BASE_URL ?>/rapports/rapport-journalier.php" class="<?= $current_page == 'rapport-journalier.php' ? 'active' : '' ?>"><i class='bx bx-stats'></i> Rapports</a></li>
                <?php endif; ?>
                <?php if ($user_role === ROLE_SUPER_ADMIN): ?>
                    <li><a href="<?= BASE_URL ?>/modules/admin/gestion-comptes.php" class="<?= $current_page == 'gestion-comptes.php' ? 'active' : '' ?>"><i class='bx bx-group'></i> Gestion utilisateurs</a></li>
                <?php endif; ?>
                <li><a href="<?= BASE_URL ?>/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><i class='bx bx-home'></i> Accueil</a></li>
            </ul>
        </nav>
        
        <div class="user-info">
            <i class='bx bx-user-circle'></i>
            <span class="user-name"><?= htmlspecialchars($user_nom) ?></span>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-link"><i class='bx bx-log-out'></i> Déconnexion</a>
        </div>
    </div>
</header>
<main class="main-content">
    <div class="container">