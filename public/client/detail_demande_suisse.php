<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$demande_id = $_GET['id'] ?? 0;

if (!$demande_id) {
    header("Location: mes_demandes_suisse.php");
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔥 REQUÊTE CORRIGÉE - Avec les bonnes colonnes
    $stmt = $pdo->prepare("
        SELECT ds.*, u.name as user_name, u.email as user_email, u.phone as user_phone
        FROM demandes_suisse ds 
        LEFT JOIN users u ON ds.user_id = u.id 
        WHERE ds.id = ? AND ds.user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        header("Location: mes_demandes_suisse.php");
        exit;
    }

    // Récupérer les fichiers de la demande
    $stmt_fichiers = $pdo->prepare("
        SELECT * FROM demandes_suisse_fichiers 
        WHERE demande_id = ? 
        ORDER BY date_upload DESC
    ");
    $stmt_fichiers->execute([$demande_id]);
    $fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer l'historique des statuts
    $stmt_historique = $pdo->prepare("
        SELECT * FROM suivi_demandes_suisse 
        WHERE demande_id = ? 
        ORDER BY date_suivi DESC
    ");
    $stmt_historique->execute([$demande_id]);
    $historique = $stmt_historique->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonctions utilitaires
function getStatutBadge($statut) {
    $badges = [
        'en_attente' => 'secondary',
        'en_traitement' => 'warning',
        'approuvee' => 'success',
        'rejetee' => 'danger'
    ];
    return $badges[$statut] ?? 'secondary';
}

function getStatutText($statut) {
    $textes = [
        'en_attente' => 'En attente',
        'en_traitement' => 'En traitement',
        'approuvee' => 'Approuvée',
        'rejetee' => 'Rejetée'
    ];
    return $textes[$statut] ?? $statut;
}

function formatNiveauEtudes($niveau) {
    $niveaux = [
        'master1' => 'Master 1',
        'master2' => 'Master 2',
        'licence' => 'Licence',
        'doctorat' => 'Doctorat'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

function getTypeFichierText($type) {
    $types = [
        'passeport' => 'Passeport',
        'diplome' => 'Diplôme',
        'releve_notes' => 'Relevé de notes',
        'lettre_motivation' => 'Lettre de motivation',
        'cv' => 'Curriculum Vitae',
        'photo' => 'Photo',
        'attestation_financiere' => 'Attestation financière',
        'certificat_medical' => 'Certificat médical'
    ];
    return $types[$type] ?? $type;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Détail Demande - Suisse #<?php echo $demande_id; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #D52B1E;
      --secondary-color: #f8f9fa;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
    }
    
    .navbar-custom {
      background: linear-gradient(135deg, #D52B1E, #B22222);
    }
    
    .header-detail {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      border-left: 5px solid var(--primary-color);
    }
    
    .info-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.08);
      margin-bottom: 25px;
      transition: transform 0.3s ease;
    }
    
    .info-card:hover {
      transform: translateY(-3px);
    }
    
    .info-card .card-header {
      background: linear-gradient(135deg, var(--primary-color), #B22222);
      color: white;
      border-radius: 12px 12px 0 0 !important;
      font-weight: 600;
    }
    
    .statut-badge {
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    
    .bg-en-attente { background-color: #6c757d; color: white; }
    .bg-en-traitement { background-color: #ffc107; color: black; }
    .bg-approuvee { background-color: #28a745; color: white; }
    .bg-rejetee { background-color: #dc3545; color: white; }
    
    .fichier-item {
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }
    
    .fichier-item:hover {
      background-color: #f8f9fa;
      border-color: var(--primary-color);
    }
    
    .timeline {
      position: relative;
      padding-left: 30px;
    }
    
    .timeline::before {
      content: '';
      position: absolute;
      left: 15px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(to bottom, var(--primary-color), #dee2e6);
    }
    
    .timeline-item {
      position: relative;
      margin-bottom: 25px;
      padding: 15px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -26px;
      top: 20px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--primary-color);
      border: 3px solid white;
      box-shadow: 0 0 0 2px var(--primary-color);
    }
    
    .action-btn {
      border-radius: 25px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .info-label {
      font-weight: 600;
      color: #495057;
      min-width: 150px;
    }
    
    .info-value {
      color: #212529;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-graduation-cap"></i> Babylone Service - Suisse
      </a>
      <div class="navbar-nav ms-auto">
        <a class="nav-link" href="mes_demandes_suisse.php">
          <i class="fas fa-arrow-left"></i> Retour aux demandes
        </a>
        <a class="nav-link" href="../suisse/etudes/index.php">
          <i class="fas fa-plus-circle"></i> Nouvelle demande
        </a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <!-- En-tête détaillé -->
    <div class="header-detail">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="h3 mb-2">
            <i class="fas fa-file-alt"></i> Demande #<?php echo $demande_id; ?>
          </h1>
          <h2 class="h4 text-primary mb-3">
            <?php echo htmlspecialchars($demande['nom_formation']); ?>
          </h2>
          <div class="d-flex flex-wrap gap-3 align-items-center">
            <span class="statut-badge bg-<?php echo getStatutBadge($demande['statut']); ?>">
              <i class="fas fa-circle me-2"></i>
              <?php echo getStatutText($demande['statut']); ?>
            </span>
            <span class="text-muted">
              <i class="fas fa-university"></i>
              <?php echo htmlspecialchars($demande['etablissement']); ?>
            </span>
            <span class="text-muted">
              <i class="fas fa-calendar"></i>
              Soumis le <?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?>
            </span>
          </div>
        </div>
        <div class="col-md-4 text-end">
          <div class="btn-group">
            <?php if ($demande['statut'] === 'en_attente'): ?>
              <a href="modifier_demande_suisse.php?id=<?php echo $demande_id; ?>" 
                 class="btn btn-warning action-btn">
                <i class="fas fa-edit"></i> Modifier
              </a>
            <?php endif; ?>
            <a href="confirmation_suisse.php?id=<?php echo $demande_id; ?>" 
               class="btn btn-info action-btn">
              <i class="fas fa-print"></i> Imprimer
            </a>
            <a href="mes_demandes_suisse.php" 
               class="btn btn-outline-secondary action-btn">
              <i class="fas fa-list"></i> Liste
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Colonne gauche - Informations principales -->
      <div class="col-lg-8">
        <!-- Informations de formation -->
        <div class="card info-card">
          <div class="card-header">
            <i class="fas fa-graduation-cap me-2"></i>Informations de formation
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Formation:</span>
                  <span class="info-value"><?php echo htmlspecialchars($demande['nom_formation']); ?></span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Niveau:</span>
                  <span class="info-value"><?php echo formatNiveauEtudes($demande['niveau_etudes']); ?></span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Établissement:</span>
                  <span class="info-value"><?php echo htmlspecialchars($demande['etablissement']); ?></span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Ville:</span>
                  <span class="info-value"><?php echo htmlspecialchars($demande['ville_etablissement']); ?></span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Date début:</span>
                  <span class="info-value">
                    <?php echo $demande['date_debut_etudes'] ? date('d/m/Y', strtotime($demande['date_debut_etudes'])) : 'Non spécifiée'; ?>
                  </span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="d-flex">
                  <span class="info-label">Durée:</span>
                  <span class="info-value"><?php echo htmlspecialchars($demande['duree_etudes']); ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fichiers joints -->
        <div class="card info-card">
          <div class="card-header">
            <i class="fas fa-paperclip me-2"></i>Documents joints (<?php echo count($fichiers); ?>)
          </div>
          <div class="card-body">
            <?php if (empty($fichiers)): ?>
              <p class="text-muted text-center">Aucun document joint</p>
            <?php else: ?>
              <div class="row">
                <?php foreach ($fichiers as $fichier): ?>
                  <div class="col-md-6 mb-3">
                    <div class="fichier-item">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <i class="fas fa-file-pdf text-danger me-2"></i>
                          <strong><?php echo getTypeFichierText($fichier['type_fichier']); ?></strong>
                          <br>
                          <small class="text-muted">
                            Uploadé le <?php echo date('d/m/Y H:i', strtotime($fichier['date_upload'])); ?>
                          </small>
                        </div>
                        <a href="telecharger_fichier_suisse.php?id=<?php echo $fichier['id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-download"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Colonne droite - Informations complémentaires -->
      <div class="col-lg-4">
        <!-- Statut et historique -->
        <div class="card info-card">
          <div class="card-header">
            <i class="fas fa-history me-2"></i>Historique du statut
          </div>
          <div class="card-body">
            <?php if (empty($historique)): ?>
              <p class="text-muted text-center">Aucun historique disponible</p>
            <?php else: ?>
              <div class="timeline">
                <?php foreach ($historique as $event): ?>
                  <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <strong><?php echo getStatutText($event['statut']); ?></strong>
                      <small class="text-muted">
                        <?php echo date('d/m/Y H:i', strtotime($event['date_suivi'])); ?>
                      </small>
                    </div>
                    <?php if (!empty($event['commentaire'])): ?>
                      <p class="mb-0 small"><?php echo htmlspecialchars($event['commentaire']); ?></p>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Informations personnelles -->
        <div class="card info-card">
          <div class="card-header">
            <i class="fas fa-user me-2"></i>Informations personnelles
          </div>
          <div class="card-body">
            <div class="mb-2">
              <strong>Nom:</strong><br>
              <?php echo htmlspecialchars($demande['user_name'] ?? 'Non spécifié'); ?>
            </div>
            <div class="mb-2">
              <strong>Email:</strong><br>
              <?php echo htmlspecialchars($demande['user_email'] ?? 'Non spécifié'); ?>
            </div>
            <?php if (!empty($demande['user_phone'])): ?>
              <div class="mb-2">
                <strong>Téléphone:</strong><br>
                <?php echo htmlspecialchars($demande['user_phone']); ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($demande['adresse'])): ?>
              <div class="mb-2">
                <strong>Adresse:</strong><br>
                <?php echo htmlspecialchars($demande['adresse']); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Actions rapides -->
        <div class="card info-card">
          <div class="card-header">
            <i class="fas fa-bolt me-2"></i>Actions rapides
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="confirmation_suisse.php?id=<?php echo $demande_id; ?>" 
                 class="btn btn-outline-primary">
                <i class="fas fa-file-pdf"></i> Télécharger le reçu
              </a>
              <a href="mes_demandes_suisse.php" 
                 class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux demandes
              </a>
              <?php if ($demande['statut'] === 'en_attente'): ?>
                <button class="btn btn-outline-danger" onclick="confirmSuppression()">
                  <i class="fas fa-trash"></i> Supprimer la demande
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-graduation-cap"></i> Babylone Service</h5>
          <p>Votre partenaire pour les études en Suisse</p>
        </div>
        <div class="col-md-6 text-end">
          <p>Contact: babylone.service15@gmail.com</p>
          <p>© 2025 Tous droits réservés</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function confirmSuppression() {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
        window.location.href = 'supprimer_demande_suisse.php?id=<?php echo $demande_id; ?>';
      }
    }

    // Animation des cartes
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.info-card');
      cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
          card.style.transition = 'all 0.5s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, index * 100);
      });
    });
  </script>
</body>
</html>