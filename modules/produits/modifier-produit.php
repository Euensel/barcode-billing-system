<?php
// modules/produits/modifier_produit.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-produits.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ../../index.php');
    exit();
}

$code_barre = $_GET['code_barre'] ?? '';
$produit = trouverProduitParCodeBarre($code_barre);
if (!$produit) {
    header('Location: liste_produits.php?msg=notfound');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prix = floatval($_POST['prix_unitaire_ht'] ?? 0);
    $date_exp = trim($_POST['date_expiration'] ?? '');
    $stock = intval($_POST['quantite_stock'] ?? 0);
    
    if (empty($nom) || $prix <= 0 || $stock < 0 || empty($date_exp)) {
        $error = "Champs invalides.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_exp)) {
        $error = "Format date invalide.";
    } else {
        $newData = [
            'nom' => $nom,
            'prix_unitaire_ht' => $prix,
            'date_expiration' => $date_exp,
            'quantite_stock' => $stock
        ];
        if (modifierProduit($code_barre, $newData)) {
            $success = "Produit modifié.";
            $produit = trouverProduitParCodeBarre($code_barre);
        } else {
            $error = "Erreur modification.";
        }
    }
}

include '../../includes/header.php';
?>

<main style="padding-top: 80px;">
    <div class="product-container">
        <h1>Modifier produit : <?= htmlspecialchars($produit['nom']) ?></h1>
        <form method="post" class="product-form">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label>Prix HT (CDF)</label>
                <input type="number" step="0.01" name="prix_unitaire_ht" value="<?= $produit['prix_unitaire_ht'] ?>" required>
            </div>
            <div class="form-group">
                <label>Date expiration</label>
                <input type="date" name="date_expiration" value="<?= htmlspecialchars($produit['date_expiration']) ?>" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="quantite_stock" value="<?= $produit['quantite_stock'] ?>" required>
            </div>
            <button type="submit" class="btn-submit">Enregistrer</button>
        </form>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?> <a href="liste.php">Retour à la liste</a></div><?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>