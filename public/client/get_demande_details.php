<?php
session_start();

// Vérifier que l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de demande manquant</div>';
    exit;
}

$demande_id = intval($_GET['id']);

// Connexion BDD
require_once __DIR__ . '../../../config.php';
try {
    

    // Récupérer la demande
    $stmt = $pdo->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        echo '<div class="alert alert-danger">Demande non trouvée</div>';
        exit;
    }

    // Décoder les données JSON
    $informations_visa = json_decode($demande['informations_visa'], true) ?? [];
    $garants = json_decode($demande['garants'], true) ?? [];
    $hebergement = json_decode($demande['hebergement'], true) ?? [];
    $ressources_financieres = json_decode($demande['ressources_financieres'], true) ?? [];

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erreur BDD: ' . $e->getMessage() . '</div>';
    exit;
}

// Fonctions utilitaires
function formatTypeVisa($type) {
    $types = [
        'tourisme' => 'Tourisme',
        'etudes' => 'Études',
        'travail' => 'Travail',
        'affaires' => 'Affaires',
        'familial' => 'Familial',
        'transit' => 'Transit',
        'autre' => 'Autre'
    ];
    return $types[$type] ?? $type;
}

function formatDate($date) {
    return $date ? date('d/m/Y', strtotime($date)) : 'Non renseignée';
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nom complet:</strong><br><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></p>
                    <p><strong>Date de naissance:</strong><br><?php echo formatDate($demande['date_naissance']); ?></p>
                    <p><strong>Lieu de naissance:</strong><br><?php echo htmlspecialchars($demande['lieu_naissance']); ?></p>
                    <p><strong>Nationalité:</strong><br><?php echo htmlspecialchars($demande['nationalite']); ?></p>
                    <p><strong>Situation familiale:</strong><br><?php echo htmlspecialchars($demande['situation_familiale']); ?></p>
                    <p><strong>Profession:</strong><br><?php echo htmlspecialchars($demande['profession']); ?></p>
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($demande['email']); ?></p>
                    <p><strong>Téléphone:</strong><br><?php echo htmlspecialchars($demande['telephone']); ?></p>
                    <p><strong>Adresse:</strong><br><?php echo nl2br(htmlspecialchars($demande['adresse'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-passport me-2"></i>Passeport</h6>
                </div>
                <div class="card-body">
                    <p><strong>Numéro:</strong><br><?php echo htmlspecialchars($demande['num_passeport']); ?></p>
                    <p><strong>Date d'émission:</strong><br><?php echo formatDate($demande['date_emission_passeport']); ?></p>
                    <p><strong>Date d'expiration:</strong><br><?php echo formatDate($demande['date_expiration_passeport']); ?></p>
                    <p><strong>Autorité d'émission:</strong><br><?php echo htmlspecialchars($demande['autorite_emission']); ?></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Détails du Visa</h6>
                </div>
                <div class="card-body">
                    <p><strong>Type de visa:</strong><br><?php echo formatTypeVisa($demande['type_visa']); ?></p>
                    <p><strong>Pays de destination:</strong><br><?php echo htmlspecialchars($demande['pays_destination']); ?></p>
                    <p><strong>Date de demande:</strong><br><?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?></p>
                    <p><strong>Statut:</strong><br>
                        <span class="badge bg-<?php 
                            switch($demande['statut']) {
                                case 'en_attente': echo 'warning'; break;
                                case 'confirme': echo 'success'; break;
                                case 'refuse': echo 'danger'; break;
                                case 'annule': echo 'secondary'; break;
                                default: echo 'info';
                            }
                        ?>">
                            <?php echo ucfirst($demande['statut']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($informations_visa)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informations Spécifiques au Visa</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($informations_visa as $key => $value): ?>
                            <?php if (!empty($value)): ?>
                                <div class="col-md-6 mb-2">
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> 
                                    <span class="text-muted"><?php echo htmlspecialchars($value); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($garants)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Garants (<?php echo count($garants); ?>)</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($garants as $index => $garant): ?>
                        <div class="garant-item mb-3 p-3 border rounded">
                            <h6 class="border-bottom pb-2">Garant <?php echo $index + 1; ?></h6>
                            <div class="row">
                                <?php foreach ($garant as $key => $value): ?>
                                    <?php if (!empty($value)): ?>
                                        <div class="col-md-6 mb-2">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> 
                                            <span class="text-muted"><?php echo htmlspecialchars($value); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($hebergement['type'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-home me-2"></i>Hébergement</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($hebergement as $key => $value): ?>
                            <?php if (!empty($value)): ?>
                                <div class="col-md-6 mb-2">
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> 
                                    <span class="text-muted"><?php echo htmlspecialchars($value); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($ressources_financieres)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-euro-sign me-2"></i>Ressources Financières</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($ressources_financieres as $key => $value): ?>
                            <?php if (!empty($value)): ?>
                                <div class="col-md-6 mb-2">
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> 
                                    <span class="text-muted"><?php echo htmlspecialchars($value); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="confirmation_visa.php?id=<?php echo $demande['id']; ?>" target="_blank" class="btn btn-primary me-2">
            <i class="fas fa-print me-2"></i>Ouvrir la page d'impression
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Fermer
        </button>
    </div>
</div>