<?php
session_start();

// Vérifier que l'ID de demande est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formulaire_suisse.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_suisse WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée.");
    }

    // Récupérer les fichiers associés
    $stmt_fichiers = $pdo->prepare("SELECT type_fichier, nom_fichier_original FROM demandes_suisse_fichiers WHERE demande_id = ?");
    $stmt_fichiers->execute([$demande_id]);
    $fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le niveau d'études
function formatNiveauEtudes($niveau) {
    $niveaux = [
        'master1' => 'Master 1ère année',
        'master2' => 'Master 2ème année'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la langue de formation
function formatLangueFormation($langue) {
    $langues = [
        'allemand' => 'Allemand',
        'francais' => 'Français',
        'anglais' => 'Anglais',
        'italien' => 'Italien',
        'bilingue' => 'Bilingue'
    ];
    return $langues[$langue] ?? $langue;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation de Demande - Suisse</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #D52B1E;
      --secondary-color: #FFFFFF;
      --accent-color: #F5F5F5;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --success-color: #28a745;
      --info-color: #17a2b8;
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
      max-width: 1000px;
      margin: 20px auto;
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .header-confirmation {
      background: linear-gradient(135deg, #D52B1E, #FF6B6B);
      color: white;
      padding: 40px 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .header-confirmation::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 20px 20px;
      animation: float 20s infinite linear;
    }
    
    @keyframes float {
      0% { transform: translate(0, 0) rotate(0deg); }
      100% { transform: translate(-20px, -20px) rotate(360deg); }
    }
    
    .success-icon {
      font-size: 5rem;
      margin-bottom: 20px;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-10px); }
      60% { transform: translateY(-5px); }
    }
    
    .reference-number {
      background: rgba(255, 255, 255, 0.2);
      padding: 10px 20px;
      border-radius: 50px;
      display: inline-block;
      margin: 15px 0;
      font-size: 1.2rem;
      font-weight: bold;
      backdrop-filter: blur(10px);
    }
    
    .content {
      padding: 40px;
    }
    
    .info-section {
      background: var(--accent-color);
      padding: 25px;
      border-radius: 10px;
      margin-bottom: 30px;
      border-left: 5px solid var(--primary-color);
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .info-item {
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .info-item strong {
      color: var(--primary-color);
      display: block;
      margin-bottom: 5px;
    }
    
    .documents-section {
      background: #e8f5e8;
      border-left: 5px solid var(--success-color);
    }
    
    .documents-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }
    
    .document-item {
      background: white;
      padding: 15px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .document-item i {
      color: var(--success-color);
      font-size: 1.2rem;
    }
    
    .next-steps {
      background: #e3f2fd;
      border-left: 5px solid var(--info-color);
    }
    
    .next-steps ul {
      list-style: none;
      padding-left: 0;
    }
    
    .next-steps li {
      margin-bottom: 15px;
      padding-left: 30px;
      position: relative;
    }
    
    .next-steps li::before {
      content: "✓";
      color: var(--success-color);
      font-weight: bold;
      position: absolute;
      left: 0;
      font-size: 1.2rem;
    }
    
    .timeline {
      position: relative;
      padding-left: 30px;
      margin: 20px 0;
    }
    
    .timeline::before {
      content: '';
      position: absolute;
      left: 15px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: var(--primary-color);
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
    
    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin: 40px 0 20px;
      flex-wrap: wrap;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 15px 25px;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #D52B1E, #FF6B6B);
      color: white;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    
    .btn-success {
      background: var(--success-color);
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .footer-confirmation {
      text-align: center;
      padding: 30px;
      background: var(--accent-color);
      color: #666;
      border-top: 1px solid #ddd;
    }
    
    .contact-info {
      background: white;
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
      .action-buttons {
        flex-direction: column;
        align-items: center;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-confirmation">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1>Demande Soumise avec Succès !</h1>
      <p>Votre demande d'études en Suisse a été enregistrée avec succès</p>
      <div class="reference-number">
        <i class="fas fa-hashtag"></i>
        Référence : CH<?php echo str_pad($demande_id, 6, '0', STR_PAD_LEFT); ?>
      </div>
    </div>
    
    <div class="content">
      <!-- Résumé de la demande -->
      <div class="info-section">
        <h2><i class="fas fa-file-alt"></i> Récapitulatif de votre demande</h2>
        <div class="info-grid">
          <div class="info-item">
            <strong>Informations personnelles</strong>
            <div><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
            <div>Né(e) le : <?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?></div>
            <div>À : <?php echo htmlspecialchars($demande['lieu_naissance']); ?></div>
          </div>
          
          <div class="info-item">
            <strong>Projet d'études</strong>
            <div><?php echo formatNiveauEtudes($demande['niveau_etudes']); ?></div>
            <div><?php echo htmlspecialchars($demande['domaine_etudes']); ?></div>
            <div><?php echo htmlspecialchars($demande['etablissement']); ?></div>
          </div>
          
          <div class="info-item">
            <strong>Coordonnées</strong>
            <div><?php echo htmlspecialchars($demande['email']); ?></div>
            <div><?php echo htmlspecialchars($demande['telephone']); ?></div>
            <div>Langue : <?php echo formatLangueFormation($demande['langue_formation']); ?></div>
          </div>
        </div>
      </div>
      
      <!-- Documents téléchargés -->
      <div class="info-section documents-section">
        <h2><i class="fas fa-file-upload"></i> Documents reçus</h2>
        <p>Vos documents ont été téléchargés avec succès :</p>
        <div class="documents-list">
          <?php if (!empty($fichiers)): ?>
            <?php foreach ($fichiers as $fichier): ?>
              <div class="document-item">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($fichier['type_fichier']); ?></span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="document-item">
              <i class="fas fa-exclamation-triangle"></i>
              <span>Aucun document trouvé</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Prochaines étapes -->
      <div class="info-section next-steps">
        <h2><i class="fas fa-road"></i> Prochaines étapes</h2>
        <div class="timeline">
          <div class="timeline-item">
            <strong>Validation initiale (24-48h)</strong>
            <p>Notre équipe vérifie la complétude de votre dossier</p>
          </div>
          <div class="timeline-item">
            <strong>Contact conseiller (3-5 jours)</strong>
            <p>Un conseiller spécialisé Suisse vous contactera</p>
          </div>
          <div class="timeline-item">
            <strong>Préparation dossier (5-7 jours)</strong>
            <p>Préparation de votre dossier d'admission universitaire</p>
          </div>
          <div class="timeline-item">
            <strong>Transmission université (7-10 jours)</strong>
            <p>Envoi de votre dossier aux établissements suisses</p>
          </div>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="action-buttons">
        <a href="formulaire_suisse.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> Nouvelle demande
        </a>
        <a href="espace_utilisateur.php" class="btn btn-success">
          <i class="fas fa-user"></i> Mon espace personnel
        </a>
        <a href="index.php" class="btn btn-secondary">
          <i class="fas fa-home"></i> Retour à l'accueil
        </a>
      </div>
    </div>
    
    <div class="footer-confirmation">
      <div class="contact-info">
        <h3><i class="fas fa-life-ring"></i> Assistance</h3>
        <p>Pour toute question concernant votre demande Suisse :</p>
        <p><strong>Email :</strong> suisse@babylone-service.com</p>
        <p><strong>Téléphone :</strong> +XX XXX XXX XXX</p>
        <p><strong>Délai de traitement :</strong> 5 à 7 jours ouvrables</p>
      </div>
      <p style="margin-top: 20px;">© 2024 Babylone Service - Demande d'études en Suisse</p>
    </div>
  </div>

  <script>
    // Animation supplémentaire pour le chargement
    document.addEventListener('DOMContentLoaded', function() {
      // Animation des éléments de la timeline
      const timelineItems = document.querySelectorAll('.timeline-item');
      timelineItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
          item.style.transition = 'all 0.5s ease';
          item.style.opacity = '1';
          item.style.transform = 'translateX(0)';
        }, index * 200);
      });
      
      // Confetti animation simple
      setTimeout(() => {
        const confetti = document.createElement('div');
        confetti.innerHTML = '🎉';
        confetti.style.position = 'fixed';
        confetti.style.top = '50%';
        confetti.style.left = '50%';
        confetti.style.transform = 'translate(-50%, -50%)';
        confetti.style.fontSize = '3rem';
        confetti.style.zIndex = '1000';
        confetti.style.opacity = '0';
        confetti.style.transition = 'all 0.5s ease';
        document.body.appendChild(confetti);
        
        setTimeout(() => {
          confetti.style.opacity = '1';
          setTimeout(() => {
            confetti.style.opacity = '0';
            setTimeout(() => {
              document.body.removeChild(confetti);
            }, 500);
          }, 1000);
        }, 100);
      }, 1000);
    });
  </script>
</body>
</html>