<?php
// rapports/rapport-mensuel.php
require_once '../config/config.php';
require_once '../includes/fonctions-auth.php';
require_once '../includes/fonctions-factures.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Mois sélectionné (par défaut le mois en cours)
$mois = isset($_GET['mois']) ? $_GET['mois'] : date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mois)) {
    $mois = date('Y-m');
}
$annee = substr($mois, 0, 4);
$mois_num = substr($mois, 5, 2);

$factures = lireFactures();
// Filtrer par mois
$factures_mois = array_filter($factures, function($f) use ($mois) {
    return substr($f['date'], 0, 7) === $mois;
});

$nb_factures = count($factures_mois);
$total_ht = array_sum(array_column($factures_mois, 'total_ht'));
$total_tva = array_sum(array_column($factures_mois, 'tva'));
$total_ttc = array_sum(array_column($factures_mois, 'total_ttc'));

// Ventes par jour
$ventes_par_jour = [];
foreach ($factures_mois as $f) {
    $jour = $f['date'];
    if (!isset($ventes_par_jour[$jour])) {
        $ventes_par_jour[$jour] = 0;
    }
    $ventes_par_jour[$jour] += $f['total_ttc'];
}
// Trier par date
ksort($ventes_par_jour);

// Top 5 des meilleurs produits (optionnel)
$top_produits = [];
foreach ($factures_mois as $f) {
    foreach ($f['articles'] as $a) {
        $nom = $a['nom'];
        if (!isset($top_produits[$nom])) {
            $top_produits[$nom] = 0;
        }
        $top_produits[$nom] += $a['quantite'];
    }
}
arsort($top_produits);
$top_produits = array_slice($top_produits, 0, 5);

include '../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Rapport mensuel</h1>

        <!-- Formulaire de sélection du mois -->
        <div class="filter-bar">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label for="mois">Mois :</label>
                    <input type="month" name="mois" id="mois" value="<?= htmlspecialchars($mois) ?>">
                </div>
                <button type="submit" class="btn-submit"><i class='bx bx-calendar'></i> Voir</button>
            </form>
        </div>

        <!-- Cartes récapitulatives -->
        <div class="stats-cards">
            <div class="stat-card">
                <i class='bx bx-receipt'></i>
                <h3>Factures</h3>
                <div class="number"><?= $nb_factures ?></div>
            </div>
            <div class="stat-card">
                <i class='bx bx-dollar'></i>
                <h3>Total HT</h3>
                <div class="number"><?= number_format($total_ht, 2) ?> CDF</div>
            </div>
            <div class="stat-card">
                <i class='bx bx-tax'></i>
                <h3>TVA (18%)</h3>
                <div class="number"><?= number_format($total_tva, 2) ?> CDF</div>
            </div>
            <div class="stat-card">
                <i class='bx bx-money'></i>
                <h3>Net à payer</h3>
                <div class="number"><?= number_format($total_ttc, 2) ?> CDF</div>
            </div>
        </div>

        <!-- Graphique simple (barres en CSS) -->
        <div class="chart-container">
            <h2>Ventes par jour</h2>
            <div class="bar-chart">
                <?php if (!empty($ventes_par_jour)): ?>
                    <?php $max_valeur = max($ventes_par_jour) ?: 1; ?>
                    <?php foreach ($ventes_par_jour as $jour => $total): ?>
                        <div class="bar-item">
                            <div class="bar-label"><?= date('d/m', strtotime($jour)) ?></div>
                            <div class="bar-wrapper">
                                <div class="bar" style="width: <?= ($total / $max_valeur) * 100 ?>%;"></div>
                                <span class="bar-value"><?= number_format($total, 0) ?> CDF</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert-info">Aucune vente pour ce mois.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top produits -->
        <?php if (!empty($top_produits)): ?>
        <div class="top-products">
            <h2>Top 5 des produits les plus vendus</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Produit</th><th>Quantité vendue</th></tr></thead>
                    <tbody>
                        <?php foreach ($top_produits as $nom => $qte): ?>
                        <tr><td><?= htmlspecialchars($nom) ?></td><td><?= $qte ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des factures du mois (optionnel, avec pagination simple) -->
        <div class="invoices-list">
            <h2>Factures du mois</h2>
            <?php if ($nb_factures > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>N° facture</th><th>Date</th><th>Heure</th><th>Caissier</th><th>Total TTC</th><th>Détail</th></tr></thead>
                        <tbody>
                            <?php 
                            // Trier par date décroissante
                            $factures_triees = $factures_mois;
                            usort($factures_triees, function($a, $b) {
                                return strtotime($b['date'] . ' ' . $b['heure']) - strtotime($a['date'] . ' ' . $a['heure']);
                            });
                            foreach ($factures_triees as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['id_facture']) ?></td>
                                <td><?= htmlspecialchars($f['date']) ?></td>
                                <td><?= htmlspecialchars($f['heure']) ?></td>
                                <td><?= htmlspecialchars($f['caissier']) ?></td>
                                <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
                                <td><a href="<?= BASE_URL ?>/modules/facturation/afficher-facture.php?id=<?= urlencode($f['id_facture']) ?>" class="btn-view"><i class='bx bx-show'></i> Voir</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert-info">Aucune facture pour ce mois.</div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>