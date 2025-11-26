<?php
// Fichier: details_demande.php

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "babylone_service";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Récupérer l'ID de la demande
$demande_id = $_GET['id'] ?? null;

if (!$demande_id) {
    die("ID de demande non spécifié");
}

// Récupérer les détails de la demande
$stmt = $pdo->prepare("SELECT * FROM demandes_regroupement_familial WHERE id = ?");
$stmt->execute([$demande_id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    die("Demande non trouvée");
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'valider':
                $stmt = $pdo->prepare("UPDATE demandes_regroupement_familial SET statut = 'validé', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$demande_id]);
                $message = "Demande validée avec succès";
                // Recharger les données
                $stmt->execute([$demande_id]);
                $demande = $stmt->fetch(PDO::FETCH_ASSOC);
                break;
                
            case 'rejeter':
                $raison = $_POST['raison_rejet'] ?? '';
                $stmt = $pdo->prepare("UPDATE demandes_regroupement_familial SET statut = 'rejeté', raison_rejet = ?, date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$raison, $demande_id]);
                $message = "Demande rejetée avec succès";
                // Recharger les données
                $stmt = $pdo->prepare("SELECT * FROM demandes_regroupement_familial WHERE id = ?");
                $stmt->execute([$demande_id]);
                $demande = $stmt->fetch(PDO::FETCH_ASSOC);
                break;
        }
    }
}

// Fonction pour formater la date
function formatDate($date) {
    if (!$date) return 'Non spécifié';
    return date('d/m/Y', strtotime($date));
}

// Fonction pour formater la date et heure
function formatDateTime($datetime) {
    if (!$datetime) return 'Non spécifié';
    return date('d/m/Y à H:i', strtotime($datetime));
}

// Fonction pour afficher le statut avec badge
function getBadgeStatut($statut) {
    $classes = [
        'en_attente' => 'statut-en_attente',
        'validé' => 'statut-valide',
        'rejeté' => 'statut-rejete'
    ];
    
    $textes = [
        'en_attente' => 'En attente',
        'validé' => 'Validé',
        'rejeté' => 'Rejeté'
    ];
    
    $class = $classes[$statut] ?? 'statut-en_attente';
    $texte = $textes[$statut] ?? $statut;
    
    return "<span class='statut-badge $class'>$texte</span>";
}

// Fonction pour afficher les fichiers
function afficherFichier($nom_fichier, $dossier = "uploads/regroupement_familial/") {
    if (!$nom_fichier) return 'Aucun fichier';
    
    $chemin = $dossier . $nom_fichier;
    $extension = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));
    
    $icones = [
        'pdf' => 'fa-file-pdf',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image'
    ];
    
    $icone = $icones[$extension] ?? 'fa-file';
    
    return "
        <div class='fichier-item'>
            <i class='fas $icone'></i>
            <a href='$chemin' target='_blank' class='lien-fichier'>
                Voir le document
            </a>
        </div>
    ";
}

// Fonction pour afficher plusieurs fichiers
function afficherFichiersMultiples($noms_fichiers, $dossier = "uploads/regroupement_familial/") {
    if (!$noms_fichiers) return 'Aucun fichier';
    
    $fichiers = explode(',', $noms_fichiers);
    $html = '';
    
    foreach ($fichiers as $fichier) {
        $fichier = trim($fichier);
        if ($fichier) {
            $html .= afficherFichier($fichier, $dossier);
        }
    }
    
    return $html;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande - Regroupement Familial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0055b8;
            --secondary-blue: #2c3e50;
            --accent-red: #ce1126;
            --light-blue: #e8f2ff;
            --light-gray: #f4f7fa;
            --white: #ffffff;
            --border-color: #dbe4ee;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --success-green: #28a745;
            --warning-orange: #ffc107;
            --danger-red: #dc3545;
            --shadow: 0 4px 12px rgba(0, 85, 184, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 20px;
        }
        
        .details-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .details-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 25px 30px;
            position: relative;
        }
        
        .details-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .dossier-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
        }
        
        .dossier-number {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .statut-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .statut-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .statut-valide {
            background: #d4edda;
            color: #155724;
        }
        
        .statut-rejete {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: #004a9e;
        }
        
        .btn-success {
            background: var(--success-green);
            color: var(--white);
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: var(--danger-red);
            color: var(--white);
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: var(--warning-orange);
            color: var(--text-dark);
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .details-content {
            padding: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 25px;
            box-shadow: var(--shadow);
        }
        
        .info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-blue);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: var(--text-light);
            padding: 8px 12px;
            background: var(--light-gray);
            border-radius: 6px;
            border-left: 3px solid var(--primary-blue);
        }
        
        .fichiers-section {
            margin-top: 40px;
        }
        
        .fichiers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .fichier-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: var(--transition);
        }
        
        .fichier-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .fichier-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .lien-fichier {
            color: var(--primary-blue);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .lien-fichier:hover {
            color: #004494;
            text-decoration: underline;
        }
        
        .actions-section {
            background: var(--light-blue);
            padding: 25px;
            border-radius: 8px;
            margin-top: 40px;
            border-left: 4px solid var(--primary-blue);
        }
        
        .actions-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .message-success {
            background: var(--success-green);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .timeline {
            margin-top: 30px;
            position: relative;
        }
        
        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            padding-left: 30px;
            position: relative;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-blue);
        }
        
        .timeline-item:after {
            content: '';
            position: absolute;
            left: 5px;
            top: 12px;
            bottom: -20px;
            width: 2px;
            background: var(--border-color);
        }
        
        .timeline-item:last-child:after {
            display: none;
        }
        
        .timeline-date {
            font-weight: 600;
            color: var(--primary-blue);
            min-width: 120px;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="details-container">
        <header class="details-header">
            <h1>
                <i class="fa-solid fa-file-lines"></i>
                Détails de la Demande
            </h1>
            
            <div class="header-actions">
                <div class="dossier-info">
                    <div class="dossier-number"><?= htmlspecialchars($demande['numero_dossier']) ?></div>
                    <div><?= getBadgeStatut($demande['statut']) ?></div>
                </div>
                
                <div>
                    <a href="regroupement_familial.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </header>
        
        <div class="details-content">
            <?php if (isset($message)): ?>
                <div class="message-success">
                    <i class="fas fa-check-circle"></i> <?= $message ?>
                </div>
            <?php endif; ?>
            
            <!-- Informations générales -->
            <div class="info-grid">
                <!-- Informations du demandeur -->
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Informations du Demandeur</h3>
                    
                    <div class="info-item">
                        <span class="info-label">Nom complet</span>
                        <span class="info-value"><?= htmlspecialchars($demande['nom_complet']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date de naissance</span>
                        <span class="info-value"><?= formatDate($demande['date_naissance']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Nationalité</span>
                        <span class="info-value"><?= htmlspecialchars($demande['nationalite']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($demande['email']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value"><?= htmlspecialchars($demande['telephone']) ?></span>
                    </div>
                </div>
                
                <!-- Informations de la famille -->
                <div class="info-card">
                    <h3><i class="fas fa-users"></i> Informations Familiales</h3>
                    
                    <div class="info-item">
                        <span class="info-label">Membre de famille</span>
                        <span class="info-value"><?= htmlspecialchars($demande['nom_famille']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Lien de parenté</span>
                        <span class="info-value"><?= htmlspecialchars(ucfirst($demande['lien_parente'])) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Adresse en France</span>
                        <span class="info-value" style="white-space: pre-line;"><?= htmlspecialchars($demande['adresse_famille']) ?></span>
                    </div>
                </div>
                
                <!-- Informations du dossier -->
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Informations du Dossier</h3>
                    
                    <div class="info-item">
                        <span class="info-label">Date de soumission</span>
                        <span class="info-value"><?= formatDateTime($demande['date_soumission']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date de traitement</span>
                        <span class="info-value"><?= formatDateTime($demande['date_traitement']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Statut</span>
                        <span class="info-value"><?= getBadgeStatut($demande['statut']) ?></span>
                    </div>
                    
                    <?php if ($demande['raison_rejet']): ?>
                    <div class="info-item">
                        <span class="info-label">Raison du rejet</span>
                        <span class="info-value"><?= htmlspecialchars($demande['raison_rejet']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Commentaire -->
            <?php if ($demande['commentaire']): ?>
            <div class="info-card">
                <h3><i class="fas fa-comment"></i> Commentaire du demandeur</h3>
                <div class="info-value" style="white-space: pre-line; background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <?= htmlspecialchars($demande['commentaire']) ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Documents -->
            <div class="fichiers-section">
                <h2 style="margin-bottom: 20px; color: var(--primary-blue);">
                    <i class="fas fa-file-alt"></i> Documents fournis
                </h2>
                
                <div class="fichiers-grid">
                    <div class="fichier-card">
                        <h4><i class="fas fa-passport"></i> Passeport</h4>
                        <?= afficherFichier($demande['passeport']) ?>
                    </div>
                    
                    <div class="fichier-card">
                        <h4><i class="fas fa-id-card"></i> Titre de séjour</h4>
                        <?= afficherFichier($demande['titre_sejour']) ?>
                    </div>
                    
                    <div class="fichier-card">
                        <h4><i class="fas fa-ring"></i> Acte de mariage/naissance</h4>
                        <?= afficherFichier($demande['acte_mariage']) ?>
                    </div>
                    
                    <div class="fichier-card">
                        <h4><i class="fas fa-home"></i> Justificatif de logement</h4>
                        <?= afficherFichier($demande['justificatif_logement']) ?>
                    </div>
                    
                    <div class="fichier-card">
                        <h4><i class="fas fa-money-bill-wave"></i> Preuves de ressources</h4>
                        <?= afficherFichier($demande['ressources']) ?>
                    </div>
                    
                    <div class="fichier-card">
                        <h4><i class="fas fa-credit-card"></i> Reçu de paiement</h4>
                        <?= afficherFichier($demande['paiement']) ?>
                    </div>
                    
                    <?php if ($demande['preuves_liens']): ?>
                    <div class="fichier-card">
                        <h4><i class="fas fa-link"></i> Preuves de liens familiaux</h4>
                        <?= afficherFichiersMultiples($demande['preuves_liens']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="timeline">
                <h3 style="margin-bottom: 20px; color: var(--primary-blue);">
                    <i class="fas fa-history"></i> Historique du dossier
                </h3>
                
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDateTime($demande['date_soumission']) ?></div>
                    <div class="timeline-content">
                        <strong>Dossier créé</strong>
                        <p>La demande a été soumise avec le numéro <?= htmlspecialchars($demande['numero_dossier']) ?></p>
                    </div>
                </div>
                
                <?php if ($demande['date_traitement']): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDateTime($demande['date_traitement']) ?></div>
                    <div class="timeline-content">
                        <strong>Dossier traité</strong>
                        <p>Le dossier a été <?= $demande['statut'] ?></p>
                        <?php if ($demande['raison_rejet']): ?>
                        <p><strong>Raison :</strong> <?= htmlspecialchars($demande['raison_rejet']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Actions administrateur -->
            <?php if ($demande['statut'] === 'en_attente'): ?>
            <div class="actions-section">
                <h3><i class="fas fa-cogs"></i> Actions administrateur</h3>
                <p style="margin-bottom: 20px; color: var(--text-light);">
                    Choisissez une action pour traiter cette demande :
                </p>
                
                <div class="actions-buttons">
                    <button class="btn btn-success" onclick="validerDemande()">
                        <i class="fas fa-check"></i> Valider la demande
                    </button>
                    
                    <button class="btn btn-danger" onclick="rejeterDemande()">
                        <i class="fas fa-times"></i> Rejeter la demande
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour rejet -->
    <div class="modal" id="rejetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-times-circle"></i> Rejeter la demande</h3>
            </div>
            <form method="POST" id="rejetForm">
                <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                <input type="hidden" name="action" value="rejeter">
                
                <div class="form-group">
                    <label for="raison_rejet">Raison du rejet :</label>
                    <textarea id="raison_rejet" name="raison_rejet" required placeholder="Veuillez préciser la raison du rejet..."></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="fermerModal()">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validerDemande() {
            if (confirm('Êtes-vous sûr de vouloir valider cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                    <input type="hidden" name="action" value="valider">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function rejeterDemande() {
            document.getElementById('rejetModal').style.display = 'flex';
        }
        
        function fermerModal() {
            document.getElementById('rejetModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('rejetModal');
            if (event.target === modal) {
                fermerModal();
            }
        }
    </script>
</body>
</html>