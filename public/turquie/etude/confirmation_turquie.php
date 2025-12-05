<?php
session_start();

// Vérifier que l'ID de demande est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formulaire_turquie.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Connexion BDD
require_once __DIR__ . '/../../../config.php';
try {


    // Récupérer les informations de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_turquie WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée.");
    }

    // Récupérer les fichiers associés
    $stmt_fichiers = $pdo->prepare("SELECT type_fichier FROM demandes_turquie_fichiers WHERE demande_id = ?");
    $stmt_fichiers->execute([$demande_id]);
    $fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonctions utilitaires
function formatNiveau($niveau) {
    $niveaux = [
        'bac' => 'Baccalauréat',
        'l1' => 'Licence 1',
        'l2' => 'Licence 2',
        'l3' => 'Licence 3',
        'master' => 'Master'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

function formatLangue($langue) {
    $langues = [
        'turc' => 'Turc',
        'anglais' => 'Anglais'
    ];
    return $langues[$langue] ?? $langue;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation - Demande Turquie</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #E30A17;
      --secondary-color: #FFFFFF;
      --accent-color: #F5F5F5;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --success-color: #28a745;
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
      text-align: center;
    }
    
    .container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .header-confirmation {
      background: linear-gradient(135deg, #E30A17, #FF6B6B);
      color: white;
      padding: 40px 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
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
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
      border-left: 5px solid #2196F3;
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
      background: var(--primary-color);
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
      <p>Votre demande d'études en Turquie a été enregistrée avec succès</p>
      <div class="reference-number">
        <i class="fas fa-hashtag"></i>
        Référence : TR<?php echo str_pad($demande_id, 6, '0', STR_PAD_LEFT); ?>
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
            <div>Nationalité : <?php echo htmlspecialchars($demande['nationalite']); ?></div>
          </div>
          
          <div class="info-item">
            <strong>Projet d'études</strong>
            <div>Spécialité : <?php echo htmlspecialchars($demande['specialite']); ?></div>
            <div>Niveau : <?php echo formatNiveau($demande['niveau']); ?></div>
            <div>Langue : <?php echo formatLangue($demande['programme_langue']); ?></div>
          </div>
          
          <div class="info-item">
            <strong>Coordonnées</strong>
            <div><?php echo htmlspecialchars($demande['email']); ?></div>
            <div><?php echo htmlspecialchars($demande['telephone']); ?></div>
            <div>Date soumission : <?php echo date('d/m/Y à H:i'); ?></div>
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
            <p>Un conseiller spécialisé Turquie vous contactera</p>
          </div>
          <div class="timeline-item">
            <strong>Évaluation académique (5-7 jours)</strong>
            <p>Analyse de votre profil par nos partenaires turcs</p>
          </div>
          <div class="timeline-item">
            <strong>Préparation dossier (7-10 jours)</strong>
            <p>Préparation de votre dossier d'admission universitaire</p>
          </div>
          <div class="timeline-item">
            <strong>Transmission universités (10-14 jours)</strong>
            <p>Envoi de votre dossier aux établissements turcs</p>
          </div>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="action-buttons">
        <a href="formulaire_turquie.php" class="btn">
          <i class="fas fa-plus"></i> Nouvelle demande
        </a>
        <a href="mes_demandes_turquie.php" class="btn">
          <i class="fas fa-list"></i> Mes demandes
        </a>
        <a href="index.php" class="btn">
          <i class="fas fa-home"></i> Accueil
        </a>
      </div>
    </div>
    
    <div class="footer-confirmation">
      <div class="contact-info">
        <h3><i class="fas fa-life-ring"></i> Assistance Turquie</h3>
        <p>Pour toute question concernant votre demande :</p>
        <p><strong>Email :</strong> turquie@babylone-service.com</p>
        <p><strong>Téléphone :</strong> +XX XXX XXX XXX</p>
        <p><strong>Délai de traitement :</strong> 7 à 14 jours ouvrables</p>
      </div>
      <p style="margin-top: 20px;">© 2024 Babylone Service - Demande d'études en Turquie</p>
    </div>
  </div>

  <script>
    // Animation des éléments de la timeline
    document.addEventListener('DOMContentLoaded', function() {
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
      
      // Animation de célébration
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