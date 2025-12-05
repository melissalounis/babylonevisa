<?php
session_start();

// Vérifier si l'utilisateur est admin - CORRIGÉ sans "role"
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accès non autorisé');
}

require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID de demande non spécifié");
    }

    $demande_id = intval($_GET['id']);

    // Récupérer les détails complets de la demande
    $sql = "SELECT d.*, u.email as user_email, u.nom as user_nom, u.telephone as user_telephone
            FROM demandes_visa_travail d 
            LEFT JOIN users u ON d.user_id = u.id 
            WHERE d.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        throw new Exception("Demande non trouvée");
    }

    // Afficher les détails
    ?>
    <div class="detail-section">
        <h3>Informations personnelles</h3>
        <div class="detail-row">
            <div class="detail-label">Nom complet:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['nom_complet'] ?? ''); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Email:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['user_email'] ?? ''); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Téléphone:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['user_telephone'] ?? 'Non spécifié'); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date de naissance:</div>
            <div class="detail-value"><?php echo isset($demande['date_naissance']) ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non spécifié'; ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Lieu de naissance:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['lieu_naissance'] ?? 'Non spécifié'); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Nationalité:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['nationalite'] ?? 'Non spécifié'); ?></div>
        </div>
    </div>

    <div class="detail-section">
        <h3>Informations professionnelles</h3>
        <div class="detail-row">
            <div class="detail-label">Pays de destination:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['pays_destination'] ?? ''); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Employeur:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['employeur'] ?? ''); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Type de contrat:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['type_contrat'] ?? ''); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Salaire proposé:</div>
            <div class="detail-value"><?php echo number_format($demande['salaire_propose'] ?? 0, 0, ',', ' '); ?> €/an</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Durée du séjour:</div>
            <div class="detail-value"><?php echo $demande['duree_sejour'] ?? ''; ?> mois</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date de début:</div>
            <div class="detail-value"><?php echo isset($demande['date_debut_travail']) ? date('d/m/Y', strtotime($demande['date_debut_travail'])) : 'Non spécifié'; ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Poste occupé:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['poste_occupe'] ?? 'Non spécifié'); ?></div>
        </div>
    </div>

    <div class="detail-section">
        <h3>Statut et suivi</h3>
        <div class="detail-row">
            <div class="detail-label">Statut:</div>
            <div class="detail-value">
                <span class="statut statut-<?php echo str_replace('_', '-', $demande['statut'] ?? ''); ?>">
                    <?php 
                    $statuts = [
                        'en_attente' => 'En attente',
                        'en_cours' => 'En cours',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté'
                    ];
                    echo $statuts[$demande['statut'] ?? ''] ?? ($demande['statut'] ?? 'Inconnu');
                    ?>
                </span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Commentaire admin:</div>
            <div class="detail-value"><?php echo htmlspecialchars($demande['commentaire_admin'] ?? 'Aucun commentaire'); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date de création:</div>
            <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($demande['created_at'] ?? '')); ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Dernière mise à jour:</div>
            <div class="detail-value"><?php echo isset($demande['updated_at']) ? date('d/m/Y H:i', strtotime($demande['updated_at'])) : 'Jamais'; ?></div>
        </div>
    </div>

    <?php if (!empty($demande['documents'])): ?>
    <div class="detail-section">
        <h3>Documents joints</h3>
        <div class="documents-list">
            <?php
            $documents = json_decode($demande['documents'] ?? '[]', true);
            if (is_array($documents)) {
                foreach ($documents as $docName => $docPath) {
                    if (!empty($docPath)) {
                        echo '<a href="' . htmlspecialchars($docPath) . '" class="document-link" target="_blank">' . htmlspecialchars($docName) . '</a>';
                    }
                }
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <?php
} catch (PDOException $e) {
    echo '<div class="error">Erreur de base de données: ' . htmlspecialchars($e->getMessage()) . '</div>';
} catch (Exception $e) {
    echo '<div class="error">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>