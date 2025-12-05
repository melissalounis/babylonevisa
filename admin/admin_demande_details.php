<?php
// admin_demande_details.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Connexion à la base de données babylone_service
include '../config.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $udb_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération de la demande
if (!isset($_GET['id'])) {
    header('Location: admin_demandes.php');
    exit();
}

$demande_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM demandes_equivalences WHERE id = ?");
$stmt->execute([$demande_id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    header('Location: admin_demandes.php');
    exit();
}

// DEBUG: Afficher les données pour vérifier
// echo "<pre>"; print_r($demande); echo "</pre>";

// Fonction pour afficher les fichiers
function afficherFichiers($fichiers_joints) {
    if (empty($fichiers_joints)) {
        return '<p class="no-info">Aucun fichier joint</p>';
    }
    
    $fichiers = explode(',', $fichiers_joints);
    $html = '';
    
    foreach ($fichiers as $fichier) {
        $fichier = trim($fichier);
        if (!empty($fichier)) {
            $chemin_fichier = '../uploads/' . $fichier;
            
            // Déterminer le nom d'affichage
            $nom_affichage = $fichier;
            if (preg_match('/^[a-f0-9]+_(.+)$/', $fichier, $matches)) {
                $nom_affichage = $matches[1];
            }
            
            $nom_affichage = htmlspecialchars($nom_affichage);
            $extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
            
            // Icônes selon le type de fichier
            $icone = '📄'; // icône par défaut
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                $icone = '🖼️';
            } elseif (in_array($extension, ['pdf'])) {
                $icone = '📕';
            } elseif (in_array($extension, ['doc', 'docx'])) {
                $icone = '📘';
            }
            
            // Vérifier si le fichier existe
            if (file_exists($chemin_fichier)) {
                $taille_fichier = filesize($chemin_fichier);
                $taille_formatee = formatTailleFichier($taille_fichier);
                
                $html .= '
                <div class="fichier-item">
                    <div class="fichier-info">
                        <span class="fichier-icone">' . $icone . '</span>
                        <div>
                            <div class="fichier-nom">' . $nom_affichage . '</div>
                            <div class="fichier-details">' . $taille_formatee . ' • ' . strtoupper($extension) . '</div>
                        </div>
                    </div>
                    <div class="fichier-actions">
                        <a href="' . $chemin_fichier . '" target="_blank" class="btn btn-view">Voir</a>
                        <a href="' . $chemin_fichier . '" download="' . $nom_affichage . '" class="btn btn-download">Télécharger</a>
                    </div>
                </div>';
            } else {
                $html .= '
                <div class="fichier-item">
                    <div class="fichier-info">
                        <span class="fichier-icone">❌</span>
                        <div>
                            <div class="fichier-nom">' . $nom_affichage . '</div>
                            <div class="fichier-details" style="color: var(--danger);">Fichier non trouvé sur le serveur</div>
                        </div>
                    </div>
                </div>';
            }
        }
    }
    
    return $html;
}

// Fonction pour formater la taille du fichier
function formatTailleFichier($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Demande #<?php echo $demande['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #4bb543;
            --warning: #ffc107;
            --danger: #dc3545;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f5 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .admin-nav {
            background: var(--light);
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-light);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: var(--light);
            padding: 25px;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary);
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .info-value {
            color: var(--gray);
        }
        
        .statut {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .statut-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .statut-approuvee {
            background: #d1edff;
            color: #004085;
        }
        
        .statut-rejetee {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            margin-right: 8px;
            margin-bottom: 5px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-secondary {
            background: var(--gray);
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: var(--dark);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-view {
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-download {
            background: var(--success);
            color: white;
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }
        
        .no-info {
            color: var(--gray);
            font-style: italic;
        }
        
        .fichiers-section {
            margin-top: 30px;
        }
        
        .fichier-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: var(--transition);
        }
        
        .fichier-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }
        
        .fichier-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        
        .fichier-icone {
            font-size: 1.5rem;
        }
        
        .fichier-nom {
            font-weight: 500;
            color: var(--dark);
        }
        
        .fichier-details {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 2px;
        }
        
        .fichier-actions {
            display: flex;
            gap: 8px;
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid var(--warning);
        }
        
        @media (max-width: 768px) {
            .fichier-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .fichier-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Détails de la Demande #<?php echo $demande['id']; ?></h1>
            <p>Demande d'équivalence de <?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></p>
        </div>
        
        <div class="admin-nav">
            <div>
                <strong>Administrateur :</strong> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
            </div>
            <a href="admin_logout.php" class="logout-btn">Déconnexion</a>
        </div>
        
        <div class="content">
            <!-- Section de debug (à supprimer en production) -->
            <?php if (isset($_GET['debug'])): ?>
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                Fichiers joints dans DB: <?php echo $demande['fichiers_joints'] ?? 'Aucun'; ?><br>
                Dossier uploads existe: <?php echo is_dir('../uploads/') ? 'Oui' : 'Non'; ?>
            </div>
            <?php endif; ?>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3 class="section-title">Informations Personnelles</h3>
                    <div class="info-group">
                        <div class="info-label">Nom complet</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['telephone'] ?? 'Non renseigné'); ?></div>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3 class="section-title">Informations Académiques</h3>
                    <div class="info-group">
                        <div class="info-label">Université d'origine</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['universite_origine'] ?? 'Non renseigné'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Diplôme d'origine</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['diplome_origine'] ?? 'Non renseigné'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Filière demandée</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['filiere_demandee'] ?? 'Non renseigné'); ?></div>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3 class="section-title">Informations de Traitement</h3>
                    <div class="info-group">
                        <div class="info-label">Statut</div>
                        <div class="info-value">
                            <span class="statut statut-<?php echo $demande['statut']; ?>">
                                <?php 
                                $statut_labels = [
                                    'en_attente' => 'En attente',
                                    'approuvee' => 'Approuvée', 
                                    'rejetee' => 'Rejetée'
                                ];
                                echo $statut_labels[$demande['statut']] ?? $demande['statut'];
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Date de la demande</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></div>
                    </div>
                    <?php if (isset($demande['date_traitement']) && !empty($demande['date_traitement'])): ?>
                    <div class="info-group">
                        <div class="info-label">Date de traitement</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($demande['date_traitement'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($demande['documents']) && !empty($demande['documents'])): ?>
            <div class="info-card">
                <h3 class="section-title">Description des documents</h3>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($demande['documents'])); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($demande['notes']) && !empty($demande['notes'])): ?>
            <div class="info-card">
                <h3 class="section-title">Notes supplémentaires</h3>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($demande['notes'])); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- Section des fichiers -->
            <div class="info-card fichiers-section">
                <h3 class="section-title">Fichiers joints</h3>
                <?php 
                if (isset($demande['fichiers_joints']) && !empty($demande['fichiers_joints'])) {
                    echo afficherFichiers($demande['fichiers_joints']);
                } else {
                    echo '<p class="no-info">Aucun fichier joint à cette demande</p>';
                }
                ?>
            </div>
            
            <div class="actions">
                <a href="admin_demandes.php" class="btn btn-secondary">← Retour à la liste</a>
                
                <form method="POST" action="admin_demandes.php" style="display: inline;">
                    <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                    <?php if ($demande['statut'] === 'en_attente'): ?>
                        <button type="submit" name="action" value="traiter" class="btn btn-success">Approuver la demande</button>
                        <button type="submit" name="action" value="supprimer" class="btn btn-danger" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')">Supprimer la demande</button>
                    <?php else: ?>
                        <button type="submit" name="action" value="en_attente" class="btn btn-warning">Remettre en attente</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>