<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    die("Non autorisé");
}

// Configuration d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de la demande
    $demande_id = $_POST['demande_id'] ?? 0;
    $user_id = $_SESSION['user_id'];
    
    // Vérifier que l'utilisateur a le droit de voir cette demande
    $stmt = $pdo->prepare("
        SELECT dcs.*,
               DATE_FORMAT(dcs.date_demande, '%d/%m/%Y à %H:%i') as date_demande_format,
               DATE_FORMAT(dcs.date_naissance, '%d/%m/%Y') as date_naissance_format,
               DATE_FORMAT(dcs.date_delivrance, '%d/%m/%Y') as date_delivrance_format,
               DATE_FORMAT(dcs.date_expiration, '%d/%m/%Y') as date_expiration_format,
               CASE 
                   WHEN dcs.statut = 'en_attente' THEN 'En attente'
                   WHEN dcs.statut = 'en_cours' THEN 'En cours de traitement'
                   WHEN dcs.statut = 'approuve' THEN 'Approuvé'
                   WHEN dcs.statut = 'refuse' THEN 'Refusé'
                   ELSE dcs.statut
               END as statut_libelle
        FROM demandes_court_sejour dcs
        WHERE dcs.id = ? AND dcs.user_id = ?
    ");
    
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        echo "<p>Demande non trouvée ou accès non autorisé.</p>";
        exit;
    }
    
    // Récupérer les fichiers associés
    $stmt_files = $pdo->prepare("
        SELECT * FROM demandes_court_sejour_fichiers 
        WHERE demande_id = ? 
        ORDER BY type_fichier, date_upload
    ");
    $stmt_files->execute([$demande_id]);
    $fichiers = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur BDD détails demande: " . $e->getMessage());
    echo "<p>Erreur lors de la récupération des détails.</p>";
    exit;
}

// Mapper les types de fichiers vers des libellés lisibles
$type_fichiers_labels = [
    'copie_passeport' => 'Copie du passeport',
    'documents_travail' => 'Documents de travail',
    'lettre_invitation' => 'Lettre d\'invitation',
    'justificatif_ressources' => 'Justificatif de ressources',
    'lettre_prise_en_charge' => 'Lettre de prise en charge',
    'prise_en_charge_entreprise' => 'Prise en charge entreprise',
    'invitation_entreprise' => 'Invitation entreprise',
    'copie_visa' => 'Copie de visa précédent'
];

// Mapper les types de visa
$type_visa_labels = [
    'tourisme' => 'Visa Tourisme',
    'affaires' => 'Visa Affaires',
    'visite_familiale' => 'Visite Familiale',
    'autre' => 'Autre Type'
];
?>

<div class="demande-details">
    <div class="detail-section">
        <h4><i class="fas fa-info-circle"></i> Informations générales</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Numéro de demande:</span>
                <span class="detail-value">#<?php echo htmlspecialchars($demande['id']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date de demande:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['date_demande_format']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Statut:</span>
                <span class="detail-value status-badge status-<?php echo htmlspecialchars($demande['statut']); ?>">
                    <?php echo htmlspecialchars($demande['statut_libelle']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4><i class="fas fa-globe"></i> Destination et type de visa</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Pays de destination:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['pays_destination']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Type de visa:</span>
                <span class="detail-value"><?php echo htmlspecialchars($type_visa_labels[$demande['visa_type']] ?? $demande['visa_type']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4><i class="fas fa-user"></i> Informations personnelles</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Nom complet:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date de naissance:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['date_naissance_format']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Lieu de naissance:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['lieu_naissance']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">État civil:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['etat_civil']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nationalité:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['nationalite']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Profession:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['profession']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Adresse:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['adresse']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Téléphone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['telephone']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['email']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4><i class="fas fa-passport"></i> Passeport</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Numéro de passeport:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['passeport']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pays de délivrance:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['pays_delivrance']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date de délivrance:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['date_delivrance_format']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date d'expiration:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['date_expiration_format']); ?></span>
            </div>
        </div>
    </div>
    
    <?php if ($demande['a_deja_visa'] === 'oui'): ?>
    <div class="detail-section">
        <h4><i class="fas fa-history"></i> Visas précédents</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">A déjà eu un visa:</span>
                <span class="detail-value">Oui</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nombre de visas:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['nb_visas']); ?></span>
            </div>
            <?php if (!empty($demande['details_voyages'])): ?>
            <div class="detail-item full-width">
                <span class="detail-label">Détails des voyages:</span>
                <span class="detail-value"><?php echo htmlspecialchars($demande['details_voyages']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($fichiers)): ?>
    <div class="detail-section">
        <h4><i class="fas fa-paperclip"></i> Fichiers joints (<?php echo count($fichiers); ?>)</h4>
        <div class="files-list">
            <?php foreach ($fichiers as $fichier): ?>
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-file file-icon"></i>
                        <span class="file-name">
                            <?php echo htmlspecialchars($type_fichiers_labels[$fichier['type_fichier']] ?? $fichier['type_fichier']); ?>
                        </span>
                    </div>
                    <div class="file-actions">
                        <a href="/uploads/visas/<?php echo htmlspecialchars($fichier['chemin_fichier']); ?>" target="_blank">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.detail-section {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.detail-section h4 {
    color: #003366;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.detail-section h4 i {
    margin-right: 8px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-weight: 600;
    color: #666;
    margin-right: 10px;
}

.detail-value {
    color: #333;
    text-align: right;
}

.files-list {
    margin-top: 10px;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-item {
        flex-direction: column;
    }
    
    .detail-value {
        text-align: left;
        margin-top: 5px;
    }
}
</style>