<?php
// modules/produits/enregistrer.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-produits.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$message = '';
$error = '';
$produitExistant = null;
$codeBarreScanne = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeBarre = trim($_POST['code_barre'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prix = floatval($_POST['prix_unitaire_ht'] ?? 0);
    $dateExp = $_POST['date_expiration'] ?? '';
    $quantite = intval($_POST['quantite_stock'] ?? 0);

    if (empty($codeBarre) || empty($nom) || empty($dateExp) || $prix <= 0 || $quantite < 0) {
        $error = "Tous les champs sont obligatoires, prix > 0 et quantité ≥ 0.";
    } elseif (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateExp)) {
        $error = "Format de date invalide (MM-JJ-AAAA)";
    } else {
        $dateExpFormatee = DateTime::createFromFormat('m-d-Y', $dateExp);
        if (!$dateExpFormatee) {
            $error = "Date d'expiration invalide.";
        } else {
            $dateExpStock = $dateExpFormatee->format('Y-m-d');
            $produit = [
                'code_barre' => $codeBarre,
                'nom' => $nom,
                'prix_unitaire_ht' => $prix,
                'date_expiration' => $dateExpStock,
                'quantite_stock' => $quantite,
                'date_enregistrement' => date('Y-m-d')
            ];
            if (ajouterProduit($produit)) {
                $message = "Produit enregistré avec succès.";
                $codeBarreScanne = '';
                $produitExistant = null;
            } else {
                $error = "Ce code-barres existe déjà.";
            }
        }
    }
}

if (isset($_GET['code_barre'])) {
    $codeBarreScanne = trim($_GET['code_barre']);
    $produitExistant = trouverProduitParCodeBarre($codeBarreScanne);
}

include '../../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Enregistrement de produit</h1>

        <div class="scanner-area">
            <button type="button" id="scan-btn" class="btn-scan">
                <i class='bx bx-camera'></i> Scanner un code-barres
            </button>
            <video id="scanner-video" style="display:none;"></video>
            <div id="scanner-result" class="scanner-result"></div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="product-form-wrapper">
            <h2><?= $produitExistant ? 'Produit existant' : 'Nouveau produit' ?></h2>
            <form method="post" class="product-form">
                <div class="form-group">
                    <label>Code-barres</label>
                    <input type="text" name="code_barre" id="code_barre" value="<?= htmlspecialchars($codeBarreScanne) ?>" required>
                </div>
                <div class="form-group">
                    <label>Nom du produit</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($produitExistant['nom'] ?? '') ?>" <?= $produitExistant ? 'readonly' : '' ?> required>
                </div>
                <div class="form-group">
                    <label>Prix unitaire HT (CDF)</label>
                    <input type="number" step="any" name="prix_unitaire_ht" value="<?= htmlspecialchars($produitExistant['prix_unitaire_ht'] ?? '') ?>" <?= $produitExistant ? 'readonly' : '' ?> required>
                </div>
                <div class="form-group">
                    <label>Date d'expiration (MM-JJ-AAAA)</label>
                    <input type="text" name="date_expiration" placeholder="MM-JJ-AAAA" value="<?= $produitExistant ? date('m-d-Y', strtotime($produitExistant['date_expiration'])) : '' ?>" <?= $produitExistant ? 'readonly' : '' ?> required>
                </div>
                <div class="form-group">
                    <label>Quantité en stock</label>
                    <input type="number" name="quantite_stock" value="<?= htmlspecialchars($produitExistant['quantite_stock'] ?? '') ?>" <?= $produitExistant ? 'readonly' : '' ?> required>
                </div>
                <?php if (!$produitExistant): ?>
                    <button type="submit" class="btn-submit"><i class='bx bx-save'></i> Enregistrer le produit</button>
                <?php else: ?>
                    <p class="info-existing"><i class='bx bx-info-circle'></i> Ce produit est déjà enregistré. Vous pouvez modifier son stock depuis la <a href="<?= BASE_URL ?>/modules/produits/liste.php">liste des produits</a>.</p>
                <?php endif; ?>
            </form>
        </div>

    </div>
</div>

<script type="text/javascript" src="https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/scanner.js"></script>

<?php include '../../includes/footer.php'; ?>