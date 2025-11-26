<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les demandes de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT ds.*, 
               COUNT(df.id) as nb_fichiers,
               (SELECT MAX(date_suivi) FROM suivi_demandes_suisse sds WHERE sds.demande_id = ds.id) as dernier_suivi
        FROM demandes_suisse ds 
        LEFT JOIN demandes_suisse_fichiers df ON ds.id = df.demande_id 
        WHERE ds.user_id = ? 
        GROUP BY ds.id 
        ORDER BY ds.date_creation DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'en_attente') as en_attente,
            SUM(statut = 'en_traitement') as en_traitement,
            SUM(statut = 'approuvee') as approuvees,
            SUM(statut = 'rejetee') as rejetees
        FROM demandes_suisse 
        WHERE user_id = ?
    ");
    $stmt_stats->execute([$user_id]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

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
        'master2' => 'Master 2'
    ];
    return $niveaux[$niveau] ?? $niveau;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Demandes - Suisse</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #D52B1E;
      --secondary-color: #f8f9fa;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #17a2b8;
    }
    
    .navbar-custom {
      background: linear-gradient(135deg, #D52B1E, #B22222);
    }
    
    .stat-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
    }
    
    .demande-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      transition: all 0.3s ease;
      border-left: 4px solid var(--primary-color);
    }
    
    .demande-card:hover {
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
      transform: translateY(-2px);
    }
    
    .statut-badge {
      padding: 8px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }
    
    .bg-en-attente { background-color: #6c757d; color: white; }
    .bg-en-traitement { background-color: #ffc107; color: black; }
    .bg-approuvee { background-color: #28a745; color: white; }
    .bg-rejetee { background-color: #dc3545; color: white; }
    
    .progress-bar-custom {
      background: linear-gradient(45deg, #D52B1E, #FF6B6B);
    }
    
    .action-btn {
      border-radius: 25px;
      padding: 8px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: #dee2e6;
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
      background: #e9ecef;
    }
    
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -23px;
      top: 5px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--primary-color);
      border: 3px solid white;
      box-shadow: 0 0 0 2px var(--primary-color);
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
        <a class="nav-link" href="../suisse/etudes/index.php">
          <i class="fas fa-plus-circle"></i> Nouvelle demande
        </a>
        <a class="nav-link" href="index.php">
          <i class="fas fa-user"></i> Mon compte
        </a>
        <a class="nav-link" href="logout.php">
          <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <!-- En-tête -->
    <div class="row mb-4">
      <div class="col-12">
        <h1><i class="fas fa-list-alt"></i> Mes Demandes pour la Suisse</h1>
        <p class="text-muted">Suivez l'état d'avancement de vos demandes d'études en Suisse</p>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card stat-card bg-primary text-white">
          <div class="card-body text-center">
            <h3><?php echo $stats['total'] ?? 0; ?></h3>
            <p class="mb-0">Total des demandes</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card bg-warning text-dark">
          <div class="card-body text-center">
            <h3><?php echo $stats['en_attente'] ?? 0; ?></h3>
            <p class="mb-0">En attente</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white">
          <div class="card-body text-center">
            <h3><?php echo $stats['en_traitement'] ?? 0; ?></h3>
            <p class="mb-0">En traitement</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card bg-success text-white">
          <div class="card-body text-center">
            <h3><?php echo $stats['approuvees'] ?? 0; ?></h3>
            <p class="mb-0">Approuvées</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des demandes -->
    <div class="row">
      <div class="col-12">
        <?php if (empty($demandes)): ?>
          <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Aucune demande pour le moment</h3>
            <p class="text-muted">Vous n'avez pas encore soumis de demande d'études en Suisse.</p>
            <a href="../suisse/etude/index.php" class="btn btn-primary btn-lg">
              <i class="fas fa-plus"></i> Créer ma première demande
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($demandes as $demande): ?>
            <div class="card demande-card">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h5 class="card-title mb-0">
                        <?php echo htmlspecialchars($demande['nom_formation']); ?>
                      </h5>
                      <span class="statut-badge bg-<?php echo getStatutBadge($demande['statut']); ?>">
                        <?php echo getStatutText($demande['statut']); ?>
                      </span>
                    </div>
                    
                    <p class="text-muted mb-2">
                      <i class="fas fa-university"></i>
                      <?php echo htmlspecialchars($demande['etablissement']); ?> - 
                      <?php echo htmlspecialchars($demande['ville_etablissement']); ?>
                    </p>
                    
                    <div class="row text-muted small">
                      <div class="col-sm-4">
                        <i class="fas fa-layer-group"></i>
                        <?php echo formatNiveauEtudes($demande['niveau_etudes']); ?>
                      </div>
                      <div class="col-sm-4">
                        <i class="fas fa-calendar"></i>
                        Soumis le <?php echo date('d/m/Y', strtotime($demande['date_creation'])); ?>
                      </div>
                      <div class="col-sm-4">
                        <i class="fas fa-file"></i>
                        <?php echo $demande['nb_fichiers']; ?> documents
                      </div>
                    </div>
                    
                    <!-- Timeline simplifiée -->
                    <div class="timeline mt-3">
                      <div class="timeline-item">
                        <small class="text-muted">
                          <i class="fas fa-clock"></i>
                          Dernière mise à jour : 
                          <?php echo $demande['dernier_suivi'] ? date('d/m/Y H:i', strtotime($demande['dernier_suivi'])) : 'Aucune'; ?>
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4 text-end">
                    <div class="btn-group-vertical">
                      <a href="detail_demande_suisse.php?id=<?php echo $demande['id']; ?>" 
                         class="btn btn-outline-primary action-btn mb-2">
                        <i class="fas fa-eye"></i> Détails
                      </a>
                      
                      <?php if ($demande['statut'] === 'en_attente'): ?>
                        <a href="modifier_demande_suisse.php?id=<?php echo $demande['id']; ?>" 
                           class="btn btn-outline-warning action-btn mb-2">
                          <i class="fas fa-edit"></i> Modifier
                        </a>
                      <?php endif; ?>
                      
                      <a href="confirmation_suisse.php?id=<?php echo $demande['id']; ?>" 
                         class="btn btn-outline-info action-btn">
                        <i class="fas fa-print"></i> Reçu
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination et actions globales -->
    <div class="row mt-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted">
            Affichage de <?php echo count($demandes); ?> demande(s)
          </span>
        </div>
        <div>
          <a href="../suisse/etudes/index.php.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nouvelle demande
          </a>
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
          </a>
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
    // Animation des cartes au chargement
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.demande-card');
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

    // Confirmation pour la suppression (si implémentée plus tard)
    function confirmSuppression(demandeId) {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')) {
        window.location.href = 'supprimer_demande_suisse.php?id=' + demandeId;
      }
    }
  </script>
</body>
</html>