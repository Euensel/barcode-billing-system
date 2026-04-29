<?php

require_once __DIR__ . '/../config/config.php';

// Lit toutes les factures depuis le fichier JSON et retourne un tableau
function lireFactures() {
    if (!file_exists(INVOICES_FILE)) {
        return [];
    }
    $json = file_get_contents(INVOICES_FILE);
    return json_decode($json, true) ?? [];
}

// Écrit la liste des factures dans le fichier JSON
function ecrireFactures($factures) {
    $dir = dirname(INVOICES_FILE);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    return file_put_contents(INVOICES_FILE, json_encode($factures, JSON_PRETTY_PRINT)) !== false;
}

// Génère un identifiant unique pour une nouvelle facture (ex: FAC-20250429-001)
function genererIdFacture() {
    $factures = lireFactures();
    $today = date('Ymd');
    $max = 0;
    foreach ($factures as $f) {
        if (strpos($f['id_facture'], "FAC-$today-") === 0) {
            $num = intval(substr($f['id_facture'], -3));
            if ($num > $max) $max = $num;
        }
    }
    $next = str_pad($max + 1, 3, '0', STR_PAD_LEFT);
    return "FAC-$today-$next";
}

// Ajoute une nouvelle facture dans le fichier JSON
function ajouterFacture($facture) {
    $factures = lireFactures();
    $factures[] = $facture;
    return ecrireFactures($factures);
}

// Calcule le montant de la TVA à partir du montant hors taxe et du taux défini
function calculerTva($montantHt, $taux = TVA) {
    return round($montantHt * $taux, 2);
}
?>