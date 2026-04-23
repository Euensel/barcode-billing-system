<?php
// modules/facturation/mes_factures.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';
require_once '../../includes/fonctions-factures.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$user = $_SESSION['user'];
$factures = lireFactures();
if ($user['role'] === ROLE_CAISSIER) {
    $factures = array_filter($factures, function($f) use ($user) {
        return $f['caissier'] === $user['identifiant'];
    });
}
usort($factures, function($a, $b) {
    return strtotime($b['date'] . ' ' . $b['heure']) - strtotime($a['date'] . ' ' . $a['heure']);
});

$total_factures = count($factures);
include '../../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <div class="factures-header">
            <h1>Mes factures</h1>
            <div class="factures-count">
                <i class='bx bx-receipt'></i> <span><?= $total_factures ?></span> facture<?= $total_factures > 1 ? 's' : '' ?>
            </div>
        </div>

        <?php if (empty($factures)): ?>
            <div class="alert-info">
                <i class='bx bx-info-circle' style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Aucune facture trouvée pour le moment.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° facture</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Total TTC</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['id_facture']) ?></td>
                            <td><?= htmlspecialchars($f['date']) ?></td>
                            <td><?= htmlspecialchars($f['heure']) ?></td>
                            <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
                            <td><a href="afficher-facture.php?id=<?= urlencode($f['id_facture']) ?>" class="btn-view"><i class='bx bx-show'></i> Voir</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>