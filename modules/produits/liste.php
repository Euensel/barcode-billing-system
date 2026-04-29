<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-produits.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$produits = lireProduits();

include '../../includes/header.php';
?>

<div class="main-content">
    <div class="container">
        <h1>Liste des produits</h1>
        <p><a href="<?= BASE_URL ?>/modules/produits/enregistrer.php" class="btn-add"><i class='bx bx-plus-circle'></i> Nouveau produit</a></p>
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'supprime'): ?>
                <div class="alert alert-success"><i class='bx bx-check-circle'></i> Produit supprimé avec succès.</div>
            <?php elseif ($_GET['msg'] === 'erreur'): ?>
                <div class="alert alert-error"><i class='bx bx-error-circle'></i> Erreur lors de la suppression.</div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (empty($produits)): ?>
            <div class="alert alert-info"><i class='bx bx-info-circle'></i> Aucun produit enregistré.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code-barres</th>
                            <th>Nom</th>
                            <th>Prix HT (CDF)</th>
                            <th>Date expiration</th>
                            <th>Stock</th>
                            <th>Date enreg.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['code_barre']) ?></td>
                            <td><?= htmlspecialchars($p['nom']) ?></td>
                            <td><?= number_format($p['prix_unitaire_ht'], 2) ?></td>
                            <td><?= htmlspecialchars($p['date_expiration']) ?></td>
                            <td><?= $p['quantite_stock'] ?></td>
                            <td><?= htmlspecialchars($p['date_enregistrement']) ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>/modules/produits/modifier-produit.php?code_barre=<?= urlencode($p['code_barre']) ?>" class="btn-edit" title="Modifier"><i class='bx bx-edit-alt'></i></a>
                                <a href="<?= BASE_URL ?>/modules/produits/supprimer-produit.php?code_barre=<?= urlencode($p['code_barre']) ?>" class="btn-delete" onclick="return confirm('Supprimer définitivement ce produit ?')" title="Supprimer"><i class='bx bx-trash'></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
               
</div>



<?php include '../../includes/footer.php'; ?>