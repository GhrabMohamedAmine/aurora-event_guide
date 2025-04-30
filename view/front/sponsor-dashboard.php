<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controller/SponsorController.php';
require_once __DIR__ . '/../../controller/DemandeSponsoringController.php';

// Initialize controllers
$sponsorController = new SponsorController();
$demandeController = new DemandeSponsoringController();

// Get all sponsors
$sponsors = $sponsorController->getSponsors();
$totalSponsors = count($sponsors);

// Get all sponsorship requests
$demandes = $demandeController->getAll();
$totalDemandes = count($demandes);

// Count requests by status - ensure values are at least 0 
$enAttente = count($demandeController->getByStatus('enattente')) ?: 0;
$acceptees = count($demandeController->getByStatus('accepter')) ?: 0;
$refusees = count($demandeController->getByStatus('refuser')) ?: 0;

// If all values are 0, add dummy value for visualization
$hasData = ($enAttente > 0 || $acceptees > 0 || $refusees > 0);
if (!$hasData) {
    // Add dummy data for visualization
    $enAttente = 1;
}

// Calculate total sponsorship amount
$totalMontant = 0;
foreach ($demandes as $demande) {
    if ($demande['statut'] == 'accepter') {
        $totalMontant += $demande['montant'];
    }
}

// Get recent sponsorship requests (last 5)
$recentDemandes = array_slice($demandes, 0, 5);
?>

<div class="dashboard-section">
    <h2 class="section-title"><i class="fas fa-chart-line"></i> Tableau de Bord Sponsoring</h2>
    <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Suivez en temps réel les statistiques et les dernières demandes de sponsoring pour vos événements.</p>
    
    <div class="stat-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalSponsors; ?></h3>
                <p>Sponsors Enregistrés</p>
                <div class="stat-progress" title="Croissance mensuelle">
                    <i class="fas fa-arrow-up"></i> +12%
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(45deg, #FF6B6B, #FFE66D);">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalDemandes; ?></h3>
                <p>Demandes Totales</p>
                <div class="stat-progress">
                    <span class="badge badge-success"><?php echo $acceptees; ?> acceptées</span>
                    <span class="badge badge-warning"><?php echo $enAttente; ?> en attente</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(45deg, #43A047, #66BB6A);">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($totalMontant, 0, ',', ' '); ?> €</h3>
                <p>Montant Total Accepté</p>
                <div class="stat-progress">
                    <?php if ($totalDemandes > 0): ?>
                    <span class="stat-avg"><?php echo number_format($totalMontant/($acceptees ?: 1), 0, ',', ' '); ?> € par sponsor</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(45deg, #FFA000, #FFCA28);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $enAttente; ?></h3>
                <p>En Attente</p>
                <div class="stat-progress">
                    <div class="progress-bar">
                        <?php $ratio = $totalDemandes > 0 ? ($enAttente / $totalDemandes) * 100 : 0; ?>
                        <div class="progress-fill" style="width: <?php echo $ratio; ?>%"></div>
                    </div>
                    <span class="progress-text"><?php echo round($ratio); ?>% du total</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-chart">
            <h3><i class="fas fa-chart-pie"></i> État des Demandes</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
                <?php if (!$hasData): ?>
                <div class="no-data-overlay">
                    <p>Aucune donnée disponible</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #ffcd00;"></span>
                    <span class="legend-label">En attente (<?php echo $hasData ? $enAttente : 0; ?>)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #85E47D;"></span>
                    <span class="legend-label">Acceptées (<?php echo $acceptees; ?>)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #FF6B6B;"></span>
                    <span class="legend-label">Refusées (<?php echo $refusees; ?>)</span>
                </div>
            </div>
        </div>
        
        <div class="recent-requests">
            <h3><i class="fas fa-history"></i> Demandes Récentes</h3>
            <div class="filter-bar">
                <select id="status-filter" class="status-select">
                    <option value="all">Tous les statuts</option>
                    <option value="enattente">En attente</option>
                    <option value="accepter">Acceptées</option>
                    <option value="refuser">Refusées</option>
                </select>
                <div class="view-all">
                    <a href="#" class="view-link">Voir tout <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="requests-list">
                <?php if (empty($recentDemandes)): ?>
                    <p class="no-data">Aucune demande récente</p>
                <?php else: ?>
                    <?php foreach ($recentDemandes as $demande): ?>
                        <div class="request-item status-<?php echo $demande['statut']; ?>">
                            <div class="request-company">
                                <strong><?php echo htmlspecialchars($demande['entreprise']); ?></strong>
                            </div>
                            <div class="request-details">
                                <span class="request-date"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($demande['date_demande'])); ?></span>
                                <span class="request-amount"><i class="fas fa-euro-sign"></i> <?php echo number_format($demande['montant'], 0, ',', ' '); ?> €</span>
                                <span class="request-status status-<?php echo $demande['statut']; ?>">
                                    <?php 
                                    switch ($demande['statut']) {
                                        case 'enattente': echo '<i class="fas fa-clock"></i> En attente'; break;
                                        case 'accepter': echo '<i class="fas fa-check-circle"></i> Acceptée'; break;
                                        case 'refuser': echo '<i class="fas fa-times-circle"></i> Refusée'; break;
                                        default: echo $demande['statut']; break;
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="add_sponsor_front.php" class="btn"><i class="fas fa-plus-circle"></i> Ajouter un Sponsor</a>
    </div>
</div>

<!-- Chart.js for the dashboard charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the status chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'Acceptées', 'Refusées'],
            datasets: [{
                data: [<?php echo $enAttente; ?>, <?php echo $acceptees; ?>, <?php echo $refusees; ?>],
                backgroundColor: ['#ffcd00', '#85E47D', '#FF6B6B'],
                borderWidth: 1,
                borderColor: '#5a0c93'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    },
                    backgroundColor: 'rgba(106, 13, 173, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#ffcd00',
                    borderWidth: 1,
                    displayColors: true
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
    
    // Initialize status filter functionality
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const items = document.querySelectorAll('.request-item');
            
            items.forEach(function(item) {
                if (status === 'all' || item.classList.contains('status-' + status)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script> 