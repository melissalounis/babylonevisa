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

    // Récupérer les demandes de l'utilisateur - Version corrigée avec les bons noms de colonnes
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_campus_france 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter les demandes par statut - Version corrigée
    $stats = [
        'total' => 0,
        'en_attente' => 0,
        'en_traitement' => 0,
        'approuvee' => 0,  // Corrigé pour correspondre à l'admin
        'refusee' => 0     // Corrigé pour correspondre à l'admin
    ];

    foreach ($demandes as $demande) {
        $stats['total']++;
        // Gérer les différences de nommage entre admin et client
        $statut = $demande['statut'];
        if ($statut === 'approuve') $statut = 'approuvee';
        if ($statut === 'refuse') $statut = 'refusee';
        
        if (isset($stats[$statut])) {
            $stats[$statut]++;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le statut (cohérente avec l'admin)
function formatStatutClient($statut) {
    // Normaliser les statuts
    if ($statut === 'approuve') $statut = 'approuvee';
    if ($statut === 'refuse') $statut = 'refusee';
    
    $statuts = [
        'en_attente' => ['label' => '⏳ En attente', 'class' => 'en_attente'],
        'en_traitement' => ['label' => '🔧 En traitement', 'class' => 'en_traitement'],
        'approuvee' => ['label' => '✅ Approuvée', 'class' => 'approuve'],
        'refusee' => ['label' => '❌ Refusée', 'class' => 'refuse']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'en_attente'];
}

// Fonction pour formater le niveau d'études
function formatNiveauEtudeClient($niveau) {
    $niveaux = [
        'licence1' => 'Licence 1',
        'licence2' => 'Licence 2', 
        'licence3' => 'Licence 3',
        'master1' => 'Master 1',
        'master2' => 'Master 2',
        'doctorat' => 'Doctorat',
        'bts' => 'BTS',
        'dut' => 'DUT',
        'inge' => 'Ingénieur',
        'commerce' => 'Commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Demandes Campus France</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #0055a4; /* Bleu Campus France */
      --secondary-color: #ef4135; /* Rouge Campus France */
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #17a2b8;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --border-color: #dbe4ee;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
      color: var(--dark-text);
      line-height: 1.6;
      padding: 20px;
    }
    
    .container {
      max-width: 1200px;
      margin: auto;
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-color), #003366);
      color: white;
      padding: 30px;
      border-radius: var(--border-radius);
      margin-bottom: 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color) 0%, white 50%, var(--secondary-color) 100%);
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 2.2rem;
    }
    
    header p {
      opacity: 0.9;
      font-size: 1.1rem;
    }
    
    .user-info {
      background: rgba(255, 255, 255, 0.2);
      padding: 15px;
      border-radius: var(--border-radius);
      margin-top: 20px;
      display: inline-block;
      backdrop-filter: blur(10px);
    }
    
    /* Statistiques */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      padding: 25px;
      border-radius: var(--border-radius);
      text-align: center;
      box-shadow: var(--box-shadow);
      border-left: 4px solid var(--primary-color);
      transition: var(--transition);
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      margin-bottom: 10px;
    }
    
    .stat-label {
      font-size: 1rem;
      color: #666;
    }
    
    .stat-en_attente { border-left-color: var(--warning-color); }
    .stat-en_traitement { border-left-color: var(--info-color); }
    .stat-approuvee { border-left-color: var(--success-color); }
    .stat-refusee { border-left-color: var(--danger-color); }
    
    /* Actions rapides */
    .quick-actions {
      display: flex;
      gap: 15px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    
    .btn {
      padding: 12px 25px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }
    
    .btn-primary {
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      color: white;
    }
    
    .btn-success {
      background: var(--success-color);
      color: white;
    }
    
    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    /* Tableau des demandes */
    .demandes-section {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
    }
    
    .section-header {
      background: var(--light-gray);
      padding: 20px;
      border-bottom: 1px solid var(--border-color);
    }
    
    .section-header h2 {
      color: var(--primary-color);
      display: flex;
      align-items: center;
    }
    
    .section-header h2 i {
      margin-right: 10px;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    
    th {
      background: var(--light-gray);
      font-weight: 600;
      color: var(--primary-color);
    }
    
    tr:hover {
      background: #f8f9fa;
    }
    
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    .status-en_attente {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-en_traitement {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .status-approuve {
      background: #d4edda;
      color: #155724;
    }
    
    .status-refuse {
      background: #f8d7da;
      color: #721c24;
    }
    
    .action-buttons {
      display: flex;
      gap: 8px;
    }
    
    .btn-sm {
      padding: 6px 12px;
      font-size: 0.85rem;
    }
    
    .btn-info {
      background: var(--info-color);
      color: white;
    }
    
    .btn-warning {
      background: var(--warning-color);
      color: black;
    }
    
    .btn-danger {
      background: var(--danger-color);
      color: white;
    }
    
    /* Message vide */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: #ddd;
    }
    
    /* Filtres */
    .filters {
      background: var(--light-gray);
      padding: 20px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    select, input {
      padding: 8px 12px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
    }
    
    /* Informations importantes */
    .info-important {
      background: #e7f3ff;
      padding: 25px;
      border-radius: var(--border-radius);
      margin-top: 30px;
      border-left: 4px solid var(--primary-color);
    }
    
    .info-important h3 {
      color: var(--primary-color);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    
    .info-important h3 i {
      margin-right: 10px;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
    }
    
    .info-item {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    footer {
      text-align: center;
      padding: 30px;
      color: #666;
      margin-top: 40px;
      border-top: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
      .quick-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .filters {
        flex-direction: column;
        align-items: stretch;
      }
      
      .filter-group {
        justify-content: space-between;
      }
      
      th, td {
        padding: 10px 8px;
        font-size: 0.9rem;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Mes Demandes Campus France</h1>
    <p>Gérez et suivez l'état de vos demandes d'études en France</p>
    
    <div class="user-info">
      <i class="fas fa-user"></i> Connecté en tant que : <?php echo $_SESSION['user_email'] ?? 'Utilisateur'; ?>
    </div>
  </header>

  <!-- Statistiques -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-number"><?php echo $stats['total']; ?></div>
      <div class="stat-label">Total des demandes</div>
    </div>
    
    <div class="stat-card stat-en_attente">
      <div class="stat-number"><?php echo $stats['en_attente']; ?></div>
      <div class="stat-label">En attente</div>
    </div>
    
    <div class="stat-card stat-en_traitement">
      <div class="stat-number"><?php echo $stats['en_traitement']; ?></div>
      <div class="stat-label">En traitement</div>
    </div>
    
    <div class="stat-card stat-approuvee">
      <div class="stat-number"><?php echo $stats['approuvee']; ?></div>
      <div class="stat-label">Approuvées</div>
    </div>
    
    <div class="stat-card stat-refusee">
      <div class="stat-number"><?php echo $stats['refusee']; ?></div>
      <div class="stat-label">Refusées</div>
    </div>
  </div>

  <!-- Actions rapides -->
  <div class="quick-actions">
    <a href="../france/etudes/campus_france.php" class="btn btn-primary">
      <i class="fas fa-plus-circle"></i> Nouvelle demande
    </a>
    
    <a href="index.php" class="btn btn-outline">
      <i class="fas fa-user-circle"></i> Mon espace étudiant
    </a>
    
    <a href="documents_campus_france.php" class="btn btn-outline">
      <i class="fas fa-file-alt"></i> Documents requis
    </a>
    
    <a href="calendrier_procedure.php" class="btn btn-outline">
      <i class="fas fa-calendar-alt"></i> Calendrier
    </a>
  </div>

  <!-- Filtres -->
  <div class="filters">
    <div class="filter-group">
      <label for="filter-statut"><i class="fas fa-filter"></i> Filtre par statut :</label>
      <select id="filter-statut" onchange="filtrerDemandes()">
        <option value="tous">Tous les statuts</option>
        <option value="en_attente">En attente</option>
        <option value="en_traitement">En traitement</option>
        <option value="approuvee">Approuvées</option>
        <option value="refusee">Refusées</option>
      </select>
    </div>
    
    <div class="filter-group">
      <label for="search"><i class="fas fa-search"></i> Rechercher :</label>
      <input type="text" id="search" placeholder="Domaine, formation..." onkeyup="rechercherDemandes()">
    </div>
  </div>

  <!-- Tableau des demandes -->
  <div class="demandes-section">
    <div class="section-header">
      <h2><i class="fas fa-list-alt"></i> Historique de mes demandes</h2>
    </div>
    
    <div class="table-container">
      <?php if (count($demandes) > 0): ?>
        <table id="table-demandes">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Formation & Domaine</th>
              <th>Niveau</th>
              <th>Date de soumission</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($demandes as $demande): 
              $statut = formatStatutClient($demande['statut']);
            ?>
              <tr class="demande-row" data-statut="<?php echo $demande['statut']; ?>">
                <td>
                  <strong>CF-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                </td>
                <td>
                  <div class="formation-info">
                    <strong><?php echo htmlspecialchars($demande['domaine_etudes'] ?? 'Non spécifié'); ?></strong>
                    <?php if (!empty($demande['nom_formation'])): ?>
                      <br>
                      <small><?php echo htmlspecialchars($demande['nom_formation']); ?></small>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="badge bg-light text-dark">
                    <?php echo formatNiveauEtudeClient($demande['niveau_etudes']); ?>
                  </span>
                </td>
                <td>
                  <?php echo date('d/m/Y', strtotime($demande['created_at'])); ?><br>
                  <small><?php echo date('H:i', strtotime($demande['created_at'])); ?></small>
                </td>
                <td>
                  <span class="status-badge status-<?php echo $statut['class']; ?>">
                    <?php echo $statut['label']; ?>
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button onclick="afficherDetails(<?php echo $demande['id']; ?>)" 
                            class="btn btn-info btn-sm" title="Voir détails">
                      <i class="fas fa-eye"></i>
                    </button>
                    
                    <?php if ($demande['statut'] === 'en_attente'): ?>
                      <button onclick="modifierDemande(<?php echo $demande['id']; ?>)" 
                              class="btn btn-warning btn-sm" title="Modifier">
                        <i class="fas fa-edit"></i>
                      </button>
                    <?php endif; ?>
                    
                    <button onclick="imprimerDemande(<?php echo $demande['id']; ?>)" 
                            class="btn btn-secondary btn-sm" title="Imprimer">
                      <i class="fas fa-print"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>Aucune demande Campus France</h3>
          <p>Vous n'avez pas encore soumis de demande d'études en France.</p>
          <a href="../france/etudes/campus_france.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Créer ma première demande
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Informations importantes -->
  <div class="info-important">
    <h3><i class="fas fa-info-circle"></i> Informations importantes</h3>
    <div class="info-grid">
      <div class="info-item">
        <strong><i class="fas fa-clock"></i> Délais de traitement :</strong><br>
        • En attente : 2-3 jours ouvrables<br>
        • En traitement : 1-2 semaines<br>
        • Décision finale : 3-4 semaines
      </div>
      <div class="info-item">
        <strong><i class="fas fa-phone"></i> Contact :</strong><br>
        • Email : babylone.service15@gmail.com<br>
        • Téléphone : +213 554 31 00 47<br>
        • Horaires : Sam-Jeudi 8h30-17h
      </div>
      <div class="info-item">
        <strong><i class="fas fa-calendar"></i> Prochaines étapes :</strong><br>
        • Vérification documents<br>
        • Traitement dossier<br>
        • Préparation entretien
      </div>
    </div>
  </div>

  <footer>
    <p>© <?php echo date('Y'); ?> Campus France - Babylone Service. Tous droits réservés.</p>
    <p>Dernière mise à jour : <?php echo date('d/m/Y à H:i'); ?></p>
  </footer>
</div>

<script>
// Fonctions de filtrage et recherche
function filtrerDemandes() {
  const statut = document.getElementById('filter-statut').value;
  const rows = document.querySelectorAll('.demande-row');
  
  rows.forEach(row => {
    let rowStatut = row.dataset.statut;
    // Normaliser les statuts pour la comparaison
    if (rowStatut === 'approuve') rowStatut = 'approuvee';
    if (rowStatut === 'refuse') rowStatut = 'refusee';
    
    if (statut === 'tous' || rowStatut === statut) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

function rechercherDemandes() {
  const searchTerm = document.getElementById('search').value.toLowerCase();
  const rows = document.querySelectorAll('.demande-row');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

// Fonctions d'actions
function afficherDetails(demandeId) {
  alert('Détails de la demande #' + demandeId + ' - À implémenter');
  // window.location.href = 'details_demande.php?id=' + demandeId;
}

function modifierDemande(demandeId) {
  if (confirm('Voulez-vous modifier cette demande ?')) {
    alert('Modification demande #' + demandeId + ' - À implémenter');
    // window.location.href = 'modifier_demande.php?id=' + demandeId;
  }
}

function imprimerDemande(demandeId) {
  window.open('imprimer_demande.php?id=' + demandeId, '_blank');
}

// Auto-refresh toutes les 5 minutes pour les demandes en cours
setInterval(() => {
  const demandesEnCours = document.querySelectorAll('[data-statut="en_attente"], [data-statut="en_traitement"]');
  if (demandesEnCours.length > 0) {
    console.log('Vérification des mises à jour des statuts...');
    // Optionnel: faire un appel AJAX pour rafraîchir les statuts
  }
}, 300000);

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
  console.log('Page des demandes Campus France (client) chargée');
  console.log('Nombre de demandes : <?php echo count($demandes); ?>');
});
</script>
</body>
</html>