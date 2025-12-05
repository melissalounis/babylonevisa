<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'ID de la demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_suisse.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Connexion BDD
require_once __DIR__ . '../../../config.php';

// Initialiser les variables
$demande = null;
$error_message = '';
$success_message = '';

try {
   

    // Récupérer les détails de la demande spécifique
    $stmt = $pdo->prepare("
        SELECT ds.* 
        FROM demandes_suisse ds 
        WHERE ds.id = ? AND ds.user_id = ? AND (ds.statut = 'en_attente' OR ds.statut = 'nouveau')
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        // Vérifier si la demande existe mais avec un autre statut
        $stmt_check = $pdo->prepare("SELECT * FROM demandes_suisse WHERE id = ? AND user_id = ?");
        $stmt_check->execute([$demande_id, $user_id]);
        $demande_existante = $stmt_check->fetch();
        
        if ($demande_existante) {
            $error_message = "Demande non modifiable. Seules les demandes avec le statut 'En attente' ou 'Nouveau' peuvent être modifiées. Statut actuel : '" . $demande_existante['statut'] . "'";
        } else {
            $error_message = "Demande non trouvée ou vous n'avez pas l'autorisation de la modifier.";
        }
    }

} catch (PDOException $e) {
    $error_message = "Erreur BDD : " . $e->getMessage();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $demande) {
    try {
        // Récupérer les données du formulaire
        $update_data = [
            'nom_formation' => $_POST['nom_formation'],
            'etablissement' => $_POST['etablissement'],
            'ville_etablissement' => $_POST['ville_etablissement'],
            'niveau_etudes' => $_POST['niveau_etudes'],
            'date_debut' => $_POST['date_debut'],
            'date_modification' => date('Y-m-d H:i:s')
        ];

        // Construction de la requête UPDATE
        $set_parts = [];
        $params = [];
        foreach ($update_data as $key => $value) {
            $set_parts[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $demande_id;
        $params[] = $user_id;

        $sql = "UPDATE demandes_suisse SET " . implode(', ', $set_parts) . " 
                WHERE id = ? AND user_id = ? AND (statut = 'en_attente' OR statut = 'nouveau')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $success_message = "La demande a été modifiée avec succès !";
            // Recharger les données de la demande
            $stmt = $pdo->prepare("SELECT * FROM demandes_suisse WHERE id = ? AND user_id = ?");
            $stmt->execute([$demande_id, $user_id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Aucune modification n'a été apportée ou la demande n'est plus modifiable.";
        }

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// Si la demande n'existe pas ou n'est pas modifiable, rediriger
if (!$demande && empty($error_message)) {
    header("Location: mes_demandes_suisse.php");
    exit;
}

// Fonctions utilitaires
function getStatutBadge($statut) {
    $badges = [
        'en_attente' => 'secondary',
        'nouveau' => 'primary',
        'en_traitement' => 'warning',
        'approuvee' => 'success',
        'rejetee' => 'danger'
    ];
    return $badges[$statut] ?? 'secondary';
}

function getStatutText($statut) {
    $textes = [
        'en_attente' => 'En attente',
        'nouveau' => 'Nouveau',
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier la Demande - Suisse</title>
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
    
    .form-section {
      border: none;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      transition: all 0.3s ease;
      border-left: 4px solid var(--primary-color);
      background: var(--secondary-color);
    }
    
    .form-section:hover {
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .statut-badge {
      padding: 8px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }
    
    .bg-en-attente { background-color: #6c757d; color: white; }
    .bg-nouveau { background-color: #0d6efd; color: white; }
    .bg-en-traitement { background-color: #ffc107; color: black; }
    .bg-approuvee { background-color: #28a745; color: white; }
    .bg-rejetee { background-color: #dc3545; color: white; }
    
    .action-btn {
      border-radius: 25px;
      padding: 8px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .required-label::after {
      content: " *";
      color: var(--danger-color);
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
        <a class="nav-link" href="index.php">
          <i class="fas fa-user"></i> Mon compte
        </a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <!-- En-tête -->
    <div class="row mb-4">
      <div class="col-12">
        <h1><i class="fas fa-edit"></i> Modifier la Demande</h1>
        <p class="text-muted">Modifiez les informations de votre demande d'études en Suisse</p>
      </div>
    </div>

    <!-- Messages d'alerte -->
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($demande): ?>
      <!-- Informations de la demande -->
      <div class="card form-section mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h5 class="card-title">
                <i class="fas fa-info-circle"></i> Informations de la demande
              </h5>
              <p class="text-muted mb-0">ID: #<?php echo $demande_id; ?></p>
            </div>
            <div class="col-md-6 text-end">
              <span class="statut-badge bg-<?php echo getStatutBadge($demande['statut']); ?>">
                <?php echo getStatutText($demande['statut']); ?>
              </span>
              <p class="text-muted mb-0 mt-2">
                <small>Créée le <?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?></small>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire de modification -->
      <form method="POST" action="">
        <!-- Informations de formation -->
        <div class="card form-section">
          <div class="card-body">
            <h5 class="card-title mb-4">
              <i class="fas fa-graduation-cap"></i> Informations de formation
            </h5>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="nom_formation" class="form-label required-label">Nom de la formation</label>
                <input type="text" class="form-control" id="nom_formation" name="nom_formation" 
                       value="<?php echo htmlspecialchars($demande['nom_formation']); ?>" required>
                <div class="form-text">Ex: Master en Informatique, Bachelor en Médecine...</div>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="etablissement" class="form-label required-label">Établissement</label>
                <input type="text" class="form-control" id="etablissement" name="etablissement" 
                       value="<?php echo htmlspecialchars($demande['etablissement']); ?>" required>
                <div class="form-text">Nom de l'université ou école</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="ville_etablissement" class="form-label required-label">Ville de l'établissement</label>
                <input type="text" class="form-control" id="ville_etablissement" name="ville_etablissement" 
                       value="<?php echo htmlspecialchars($demande['ville_etablissement']); ?>" required>
                <div class="form-text">Ville où se situe l'établissement</div>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="niveau_etudes" class="form-label required-label">Niveau d'études</label>
                <select class="form-select" id="niveau_etudes" name="niveau_etudes" required>
                  <option value="">-- Sélectionnez --</option>
                  <option value="licence" <?php echo $demande['niveau_etudes'] === 'licence' ? 'selected' : ''; ?>>Licence</option>
                  <option value="master1" <?php echo $demande['niveau_etudes'] === 'master1' ? 'selected' : ''; ?>>Master 1</option>
                  <option value="master2" <?php echo $demande['niveau_etudes'] === 'master2' ? 'selected' : ''; ?>>Master 2</option>
                  <option value="doctorat" <?php echo $demande['niveau_etudes'] === 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="date_debut" class="form-label required-label">Date de début prévue</label>
                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                       value="<?php echo htmlspecialchars($demande['date_debit'] ?? ''); ?>" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="card form-section">
          <div class="card-body">
            <div class="row">
              <div class="col-12 d-flex justify-content-between">
                <a href="mes_demandes_suisse.php" class="btn btn-outline-secondary action-btn">
                  <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary action-btn">
                  <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>

    <?php else: ?>
      <!-- Message si demande non trouvée -->
      <div class="card form-section">
        <div class="card-body text-center py-5">
          <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
          <h3>Demande non trouvée</h3>
          <p class="text-muted">La demande que vous essayez de modifier n'existe pas ou vous n'avez pas l'autorisation d'y accéder.</p>
          <a href="mes_demandes_suisse.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Retour aux demandes
          </a>
        </div>
      </div>
    <?php endif; ?>
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
    // Animation des sections au chargement
    document.addEventListener('DOMContentLoaded', function() {
      const sections = document.querySelectorAll('.form-section');
      sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
          section.style.transition = 'all 0.5s ease';
          section.style.opacity = '1';
          section.style.transform = 'translateY(0)';
        }, index * 100);
      });

      // Validation du formulaire
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          const nomFormation = document.getElementById('nom_formation').value;
          const etablissement = document.getElementById('etablissement').value;
          const ville = document.getElementById('ville_etablissement').value;
          const niveau = document.getElementById('niveau_etudes').value;
          const dateDebut = document.getElementById('date_debut').value;
          
          if (!nomFormation || !etablissement || !ville || !niveau || !dateDebut) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
          }
        });
      }
    });
  </script>
</body>
</html>