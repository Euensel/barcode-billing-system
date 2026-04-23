<?php
// includes/fonctions_produits.php
require_once __DIR__ . '/../config/config.php';

/**
 * Lit tous les produits depuis le fichier JSON
 * @return array Tableau des produits (vide si fichier inexistant ou invalide)
 */
function lireProduits() {
    if (!file_exists(PRODUCTS_FILE)) {
        return [];
    }
    $json = file_get_contents(PRODUCTS_FILE);
    if ($json === false) {
        return [];
    }
    $data = json_decode($json, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($data)) ? $data : [];
}

/**
 * Écrit la liste des produits dans le fichier JSON
 * @param array $produits
 * @return bool
 */
function ecrireProduits($produits) {
    $dir = dirname(PRODUCTS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $json = json_encode($produits, JSON_PRETTY_PRINT);
    return file_put_contents(PRODUCTS_FILE, $json) !== false;
}

/**
 * Trouve un produit par son code-barres
 * @param string $codeBarre
 * @return array|null
 */
function trouverProduitParCodeBarre($codeBarre) {
    $produits = lireProduits();
    foreach ($produits as $p) {
        if ($p['code_barre'] === $codeBarre) {
            return $p;
        }
    }
    return null;
}

/**
 * Ajoute un nouveau produit
 * @param array $produit
 * @return bool
 */
function ajouterProduit($produit) {
    $produits = lireProduits();
    // Vérifier que le code-barres n'existe pas déjà
    if (trouverProduitParCodeBarre($produit['code_barre']) !== null) {
        return false;
    }
    $produits[] = $produit;
    return ecrireProduits($produits);
}
function modifierProduit($code_barre, $newData) {
    $produits = lireProduits();
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $code_barre) {
            $p = array_merge($p, $newData);
            return ecrireProduits($produits);
        }
    }
    return false;
}
function supprimerProduit($code_barre) {
    $produits = lireProduits();
    $newList = array_filter($produits, function($p) use ($code_barre) {
        return $p['code_barre'] !== $code_barre;
    });
    if (count($newList) === count($produits)) return false;
    return ecrireProduits(array_values($newList));
}

function decrementerStock($code_barre, $quantite) {
    $produits = lireProduits();
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $code_barre) {
            if ($p['quantite_stock'] >= $quantite) {
                $p['quantite_stock'] -= $quantite;
                return ecrireProduits($produits);
            } else {
                return false; // stock insuffisant
            }
        }
    }
    return false;
}

function modifierStockProduit($codeBarre, $nouveauStock) {
    $produits = lireProduits();
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $codeBarre) {
            $p['quantite_stock'] = $nouveauStock;
            return ecrireProduits($produits);
        }
    }
    return false;
}
?>