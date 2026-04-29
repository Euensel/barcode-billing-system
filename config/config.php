<?php

// Session démarrée UNE SEULE fois
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes
define('TVA', 0.18);
define('DATA_PATH', __DIR__ . '/../data/');
define('PRODUCTS_FILE', DATA_PATH . 'produits.json');
define('USERS_FILE', DATA_PATH . 'utilisateurs.json');
define('INVOICES_FILE', DATA_PATH . 'factures.json');

// Définition des rôles
define('ROLE_CAISSIER', 'Caissier');
define('ROLE_MANAGER', 'Manager');
define('ROLE_SUPER_ADMIN', 'Super Administrateur');
define('BASE_URL', '/barcode-billing-system');
// Permissions par rôle
function get_permissions($role) {
    $permissions = [
        ROLE_CAISSIER => ['facturation'],
        ROLE_MANAGER => ['facturation', 'produits', 'rapports'],
        ROLE_SUPER_ADMIN => ['facturation', 'produits', 'rapports', 'utilisateurs']
    ];
    return isset($permissions[$role]) ? $permissions[$role] : [];
}
?>