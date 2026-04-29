<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-factures.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$id = $_GET['id'] ?? '';
$factures = lireFactures();
$facture = null;
foreach ($factures as $f) {
    if ($f['id_facture'] === $id) {
        $facture = $f;
        break;
    }
}
if (!$facture) {
    header('Location: mes_factures.php?error=notfound');
    exit();
}
if ($_SESSION['user']['role'] === ROLE_CAISSIER && $facture['caissier'] !== $_SESSION['user']['identifiant']) {
    header('Location: mes_factures.php?error=unauthorized');
    exit();
}

include '../../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Facture <?= htmlspecialchars($facture['id_facture']) ?></h1>
        <div class="facture-header">
            <p><strong>N° facture :</strong> <?= htmlspecialchars($facture['id_facture']) ?></p>
            <p><strong>Date :</strong> <?= $facture['date'] ?> à <?= $facture['heure'] ?></p>
            <p><strong>Caissier :</strong> <?= htmlspecialchars($facture['caissier']) ?></p>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Désignation</th><th>Prix unit. HT</th><th>Quantité</th><th>Sous-total HT</th></tr></thead>
                <tbody>
                    <?php foreach ($facture['articles'] as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['nom']) ?></td>
                        <td><?= number_format($a['prix_unitaire_ht'], 2) ?> CDF</td>
                        <td><?= $a['quantite'] ?></td>
                        <td><?= number_format($a['sous_total_ht'], 2) ?> CDF</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total HT</th><th><?= number_format($facture['total_ht'], 2) ?> CDF</th></tr>
                    <tr><td colspan="3">TVA (<?= TVA * 100 ?>%)</td><td><?= number_format($facture['tva'], 2) ?> CDF</td></tr>
                    <tr><th colspan="3">Net à payer</th><th><?= number_format($facture['total_ttc'], 2) ?> CDF</th></tr>
                </tfoot>
            </table>
        </div>
        <a href="mes-factures.php" class="btn-back"><i class='bx bx-arrow-back'></i> Retour à mes factures</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>