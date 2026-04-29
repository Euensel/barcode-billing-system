<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Initialisation du panier en session
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$error = '';
$success = '';
$prefillCode = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $codeBarre = trim($_POST['code_barre'] ?? '');
        $quantite = intval($_POST['quantite'] ?? 0);
        if (empty($codeBarre) || $quantite <= 0) {
            $error = "Code-barres et quantité valide requis.";
        } else {
            $produit = trouverProduitParCodeBarre($codeBarre);
            if (!$produit) {
                $error = "Produit inconnu. Veuillez demander au manager de l'enregistrer.";
            } elseif ($quantite > $produit['quantite_stock']) {
                $error = "Stock insuffisant. Il reste {$produit['quantite_stock']} unité(s).";
            } else {
                // Fusion si déjà dans le panier
                $found = false;
                foreach ($_SESSION['panier'] as &$item) {
                    if ($item['code_barre'] === $codeBarre) {
                        $newQte = $item['quantite'] + $quantite;
                        if ($newQte > $produit['quantite_stock']) {
                            $error = "Quantité totale ($newQte) dépasse le stock ({$produit['quantite_stock']}).";
                            $found = true;
                            break;
                        }
                        $item['quantite'] = $newQte;
                        $item['sous_total_ht'] = $item['prix_unitaire_ht'] * $newQte;
                        $found = true;
                        break;
                    }
                }
                if (!$found && !$error) {
                    $_SESSION['panier'][] = [
                        'code_barre' => $produit['code_barre'],
                        'nom' => $produit['nom'],
                        'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
                        'quantite' => $quantite,
                        'sous_total_ht' => $produit['prix_unitaire_ht'] * $quantite
                    ];
                }
                if (!$error) $success = "Article ajouté.";
            }
        }
    } elseif ($action === 'clear') {
        $_SESSION['panier'] = [];
        $success = "Panier vidé.";
    } elseif ($action === 'checkout') {
        if (empty($_SESSION['panier'])) {
            $error = "Panier vide. Ajoutez des articles.";
        } else {
            // Vérifier les stocks une dernière fois
            $stockOk = true;
            foreach ($_SESSION['panier'] as $item) {
                $p = trouverProduitParCodeBarre($item['code_barre']);
                if (!$p || $item['quantite'] > $p['quantite_stock']) {
                    $error = "Stock insuffisant pour {$item['nom']}.";
                    $stockOk = false;
                    break;
                }
            }
            if ($stockOk) {
                // Décrémenter les stocks
                foreach ($_SESSION['panier'] as $item) {
                    $p = trouverProduitParCodeBarre($item['code_barre']);
                    $nouveauStock = $p['quantite_stock'] - $item['quantite'];
                    modifierStockProduit($item['code_barre'], $nouveauStock);
                }
                // Créer la facture
                $total_ht = array_sum(array_column($_SESSION['panier'], 'sous_total_ht'));
                $tva = calculerTva($total_ht);
                $total_ttc = $total_ht + $tva;
                $facture = [
                    'id_facture' => genererIdFacture(),
                    'date' => date('Y-m-d'),
                    'heure' => date('H:i:s'),
                    'caissier' => $_SESSION['user']['identifiant'],
                    'articles' => $_SESSION['panier'],
                    'total_ht' => $total_ht,
                    'tva' => $tva,
                    'total_ttc' => $total_ttc
                ];
                if (ajouterFacture($facture)) {
                    $_SESSION['panier'] = [];
                    $success = "Facture enregistrée. Numéro : {$facture['id_facture']}";
                } else {
                    $error = "Erreur lors de l'enregistrement de la facture.";
                }
            }
        }
    }
}

// Si un code-barres est passé en GET (scan), on pré-remplit le champ
if (isset($_GET['code_barre'])) {
    $prefillCode = trim($_GET['code_barre']);
}

include '../../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Nouvelle facture</h1>

        <!-- Zone de scan -->
        <div class="scanner-area">
            <button type="button" id="scan-btn" class="btn-scan"><i class='bx bx-camera'></i> Scanner un code-barres</button>
            <video id="scanner-video" style="display:none;"></video>
            <div id="scanner-result" class="scanner-result"></div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'article -->
        <div class="product-form-wrapper" style="max-width: 500px;">
            <h2>Ajouter un article</h2>
            <form method="post" class="product-form">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Code-barres</label>
                    <input type="text" name="code_barre" id="code_barre" value="<?= htmlspecialchars($prefillCode) ?>" required>
                </div>
                <div class="form-group">
                    <label>Quantité</label>
                    <input type="number" name="quantite" value="1" min="1" required>
                </div>
                <button type="submit" class="btn-submit"><i class='bx bx-cart-add'></i> Ajouter au panier</button>
            </form>
        </div>

        <!-- Affichage du panier -->
        <?php if (!empty($_SESSION['panier'])): ?>
            <div class="cart-container">
                <h2>Panier en cours</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Nom</th><th>Prix unit.</th><th>Qté</th><th>Sous-total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['panier'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nom']) ?></td>
                                <td><?= number_format($item['prix_unitaire_ht'], 2) ?> CDF</td>
                                <td><?= $item['quantite'] ?></td>
                                <td><?= number_format($item['sous_total_ht'], 2) ?> CDF</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php
                            $totalHt = array_sum(array_column($_SESSION['panier'], 'sous_total_ht'));
                            $tva = calculerTva($totalHt);
                            $totalTtc = $totalHt + $tva;
                            ?>
                            <tr><th colspan="3">Total HT</th><th><?= number_format($totalHt, 2) ?> CDF</th></tr>
                            <tr><td colspan="3">TVA (<?= TVA * 100 ?>%)</td><td><?= number_format($tva, 2) ?> CDF</td></tr>
                            <tr><th colspan="3">Net à payer</th><th><?= number_format($totalTtc, 2) ?> CDF</th></tr>
                        </tfoot>
                    </table>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <form method="post"><input type="hidden" name="action" value="clear"><button type="submit" class="btn-clear"><i class='bx bx-trash'></i> Vider le panier</button></form>
                    <form method="post"><input type="hidden" name="action" value="checkout"><button type="submit" class="btn-submit"><i class='bx bx-check'></i> Valider la facture</button></form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Scripts -->
<script type="text/javascript" src="https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/scanner.js"></script>

<?php include '../../includes/footer.php'; ?>