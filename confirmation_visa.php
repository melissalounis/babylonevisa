<?php
session_start();

// Vérifier que l'ID de rendez-vous est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formulaire_visa.php");
    exit;
}

$rendez_vous_id = intval($_GET['id']);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations du rendez-vous
    $stmt = $pdo->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmt->execute([$rendez_vous_id]);
    $rendez_vous = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rendez_vous) {
        die("Rendez-vous non trouvé.");
    }

    // Décoder les données JSON
    $informations_visa = json_decode($rendez_vous['informations_visa'], true) ?? [];
    $garants = json_decode($rendez_vous['garants'], true) ?? [];
    $hebergement = json_decode($rendez_vous['hebergement'], true) ?? [];
    $ressources_financieres = json_decode($rendez_vous['ressources_financieres'], true) ?? [];

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le type de visa
function formatTypeVisa($type) {
    $types = [
        'tourisme' => 'Visa Tourisme',
        'etudes' => 'Visa Études',
        'travail' => 'Visa Travail',
        'affaires' => 'Visa Affaires',
        'familial' => 'Visa Familial',
        'transit' => 'Visa Transit',
        'autre' => 'Autre type de visa'
    ];
    return $types[$type] ?? $type;
}

// Fonction pour formater la situation familiale
function formatSituationFamiliale($situation) {
    $situations = [
        'celibataire' => 'Célibataire',
        'marie' => 'Marié(e)',
        'divorce' => 'Divorcé(e)',
        'veuf' => 'Veuf/Veuve'
    ];
    return $situations[$situation] ?? $situation;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation de Demande de Visa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --accent-color: #ecf0f1;
      --light-gray: #f8f9fa;
      --dark-text: #2c3e50;
      --success-color: #27ae60;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
      margin: 40px auto;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 40px 30px;
      text-align: center;
    }
    
    .success-icon {
      font-size: 4rem;
      margin-bottom: 20px;
      color: white;
    }
    
    .content {
      padding: 40px;
    }
    
    .info-box {
      background: var(--accent-color);
      padding: 25px;
      border-radius: var(--border-radius);
      margin-bottom: 25px;
      border-left: 4px solid var(--secondary-color);
    }
    
    .info-box h3 {
      color: var(--primary-color);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    
    .info-box h3 i {
      margin-right: 10px;
      color: var(--secondary-color);
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-label {
      font-weight: 600;
      color: var(--primary-color);
      font-size: 0.9rem;
      margin-bottom: 5px;
    }
    
    .info-value {
      color: #555;
      font-size: 1rem;
    }
    
    .next-steps {
      background: #e8f5e8;
      padding: 25px;
      border-radius: var(--border-radius);
      margin: 30px 0;
      border-left: 4px solid var(--success-color);
    }
    
    .next-steps h3 {
      color: var(--primary-color);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    
    .next-steps h3 i {
      margin-right: 10px;
      color: var(--success-color);
    }
    
    .next-steps ul {
      list-style-type: none;
      padding-left: 0;
    }
    
    .next-steps li {
      margin-bottom: 12px;
      position: relative;
      padding-left: 25px;
    }
    
    .next-steps li:before {
      content: "✓";
      color: var(--success-color);
      font-weight: bold;
      position: absolute;
      left: 0;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 12px 25px;
      text-decoration: none;
      border-radius: var(--border-radius);
      margin: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    .btn-print {
      background: linear-gradient(to right, #27ae60, #2ecc71);
    }
    
    .btn-download {
      background: linear-gradient(to right, #e67e22, #f39c12);
    }
    
    footer {
      text-align: center;
      padding: 25px;
      background: var(--accent-color);
      color: #666;
      border-top: 1px solid #ddd;
    }
    
    .reference-number {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      text-align: center;
      margin: 20px 0;
      border: 2px dashed var(--secondary-color);
    }
    
    .reference-number .number {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--primary-color);
      margin: 10px 0;
    }
    
    .documents-list {
      background: #fff3cd;
      padding: 20px;
      border-radius: var(--border-radius);
      margin: 20px 0;
      border-left: 4px solid #ffc107;
    }
    
    .garant-item, .hebergement-item {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin: 10px 0;
      border: 1px solid #eee;
    }
    
    @media (max-width: 768px) {
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .content {
        padding: 20px;
      }
      
      .btn {
        display: block;
        text-align: center;
        margin: 10px 0;
      }
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      background: #3498db;
      color: white;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-left: 10px;
    }
    
    .status-en_attente {
      background: #f39c12;
    }
    
    .status-confirme {
      background: #27ae60;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1>Demande de Visa Enregistrée !</h1>
      <p>Votre demande de rendez-vous pour visa a été soumise avec succès</p>
    </header>
    
    <div class="content">
      <!-- Numéro de référence -->
      <div class="reference-number">
        <div class="info-label">VOTRE NUMÉRO DE RÉFÉRENCE</div>
        <div class="number">VISA-<?php echo str_pad($rendez_vous_id, 6, '0', STR_PAD_LEFT); ?></div>
        <small>Conservez précieusement ce numéro pour suivre votre dossier</small>
      </div>

      <!-- Informations générales -->
      <div class="info-box">
        <h3><i class="fas fa-info-circle"></i> Informations Générales</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Type de visa</span>
            <span class="info-value"><?php echo formatTypeVisa($rendez_vous['type_visa']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Pays de destination</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['pays_destination']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Date de soumission</span>
            <span class="info-value"><?php echo date('d/m/Y à H:i', strtotime($rendez_vous['date_creation'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Statut</span>
            <span class="info-value">
              <?php echo ucfirst(str_replace('_', ' ', $rendez_vous['statut'])); ?>
              <span class="status-badge status-<?php echo $rendez_vous['statut']; ?>">
                <?php echo strtoupper(str_replace('_', ' ', $rendez_vous['statut'])); ?>
              </span>
            </span>
          </div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="info-box">
        <h3><i class="fas fa-user"></i> Informations Personnelles</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Nom complet</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['prenom'] . ' ' . $rendez_vous['nom']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Date de naissance</span>
            <span class="info-value"><?php echo date('d/m/Y', strtotime($rendez_vous['date_naissance'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Lieu de naissance</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['lieu_naissance']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Nationalité</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['nationalite']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Situation familiale</span>
            <span class="info-value"><?php echo formatSituationFamiliale($rendez_vous['situation_familiale']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Profession</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['profession']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Téléphone</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['telephone']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['email']); ?></span>
          </div>
        </div>
      </div>

      <!-- Informations passeport -->
      <div class="info-box">
        <h3><i class="fas fa-passport"></i> Passeport</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Numéro de passeport</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['num_passeport']); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Date d'émission</span>
            <span class="info-value"><?php echo date('d/m/Y', strtotime($rendez_vous['date_emission_passeport'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Date d'expiration</span>
            <span class="info-value"><?php echo date('d/m/Y', strtotime($rendez_vous['date_expiration_passeport'])); ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Autorité d'émission</span>
            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['autorite_emission']); ?></span>
          </div>
        </div>
      </div>

      <!-- Informations spécifiques au visa -->
      <?php if (!empty($informations_visa)): ?>
      <div class="info-box">
        <h3><i class="fas fa-file-alt"></i> Informations Spécifiques au Visa</h3>
        <div class="info-grid">
          <?php foreach ($informations_visa as $key => $value): ?>
            <?php if (!empty($value)): ?>
              <div class="info-item">
                <span class="info-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></span>
                <span class="info-value"><?php echo htmlspecialchars($value); ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Garants -->
      <?php if (!empty($garants)): ?>
      <div class="info-box">
        <h3><i class="fas fa-users"></i> Garants (<?php echo count($garants); ?>)</h3>
        <?php foreach ($garants as $index => $garant): ?>
          <div class="garant-item">
            <h4>Garant <?php echo $index + 1; ?></h4>
            <div class="info-grid">
              <?php foreach ($garant as $key => $value): ?>
                <?php if (!empty($value)): ?>
                  <div class="info-item">
                    <span class="info-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></span>
                    <span class="info-value"><?php echo htmlspecialchars($value); ?></span>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Hébergement -->
      <?php if (!empty($hebergement['type'])): ?>
      <div class="info-box">
        <h3><i class="fas fa-home"></i> Hébergement</h3>
        <div class="info-grid">
          <?php foreach ($hebergement as $key => $value): ?>
            <?php if (!empty($value)): ?>
              <div class="info-item">
                <span class="info-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></span>
                <span class="info-value"><?php echo htmlspecialchars($value); ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Ressources financières -->
      <?php if (!empty($ressources_financieres['montant']) || !empty($ressources_financieres['origine_fonds'])): ?>
      <div class="info-box">
        <h3><i class="fas fa-euro-sign"></i> Ressources Financières</h3>
        <div class="info-grid">
          <?php foreach ($ressources_financieres as $key => $value): ?>
            <?php if (!empty($value)): ?>
              <div class="info-item">
                <span class="info-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></span>
                <span class="info-value"><?php echo htmlspecialchars($value); ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Documents à fournir -->
      <div class="documents-list">
        <h3><i class="fas fa-file-upload"></i> Documents à Fournir</h3>
        <p><strong>N'oubliez pas d'apporter les documents suivants lors de votre rendez-vous :</strong></p>
        <ul>
          <li>Passeport original en cours de validité</li>
          <li>Copie de toutes les pages du passeport</li>
          <li>Photos d'identité récentes</li>
          <li>Justificatifs de ressources financières</li>
          <li>Documents d'hébergement</li>
          <li>Attestation d'assurance voyage</li>
          <li>Tous les documents uploadés lors de la demande</li>
        </ul>
      </div>

      <!-- Prochaines étapes -->
      <div class="next-steps">
        <h3><i class="fas fa-list-alt"></i> Prochaines Étapes</h3>
        <ul>
          <li><strong>Email de confirmation</strong> - Vous recevrez un email de confirmation sous 24 heures</li>
          <li><strong>Vérification des documents</strong> - Notre équipe vérifiera vos documents sous 2-3 jours ouvrables</li>
          <li><strong>Rendez-vous physique</strong> - Vous serez contacté pour planifier votre rendez-vous à l'ambassade/consulat</li>
          <li><strong>Préparation de l'entretien</strong> - Préparez-vous à répondre aux questions sur votre projet de voyage</li>
          <li><strong>Délai de traitement</strong> - Le traitement complet prend généralement 10 à 15 jours ouvrables</li>
        </ul>
      </div>

      <!-- Actions -->
      <div style="text-align: center; margin: 40px 0;">
        <button onclick="window.print()" class="btn btn-print">
          <i class="fas fa-print"></i> Imprimer cette confirmation
        </button>
        
        <a href="rendez_vous.php" class="btn">
          <i class="fas fa-plus"></i> Nouvelle demande
        </a>
        
        <a href="public/index.php" class="btn">
          <i class="fas fa-home"></i> Retour à l'accueil
        </a>
      </div>
    </div>

    <footer>
      <p><strong>Besoin d'aide ?</strong> Contactez-nous à babylone.service15@gmail.com ou au +213 554 31 00 47 / 026 18 63 42</p>
      <p>© 2025 babylone service - Tous droits réservés</p>
    </footer>
  </div>

  <script>
    // Fonction pour générer un PDF (version simplifiée)
    function generatePDF() {
      alert('Fonctionnalité PDF à implémenter - Pour le moment, utilisez le bouton Imprimer');
    }

    // Animation d'apparition progressive
    document.addEventListener('DOMContentLoaded', function() {
      const elements = document.querySelectorAll('.info-box, .next-steps, .documents-list');
      elements.forEach((element, index) => {
        setTimeout(() => {
          element.style.opacity = '0';
          element.style.transform = 'translateY(20px)';
          element.style.transition = 'all 0.5s ease';
          
          setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
          }, 50);
        }, index * 200);
      });
    });

    // Ajout d'un effet de copie pour le numéro de référence
    document.querySelector('.reference-number').addEventListener('click', function() {
      const referenceNumber = 'VISA-<?php echo str_pad($rendez_vous_id, 6, '0', STR_PAD_LEFT); ?>';
      navigator.clipboard.writeText(referenceNumber).then(function() {
        const originalHTML = this.innerHTML;
        this.innerHTML = '<div style="color: #27ae60;"><i class="fas fa-check"></i> Numéro copié !</div>';
        setTimeout(() => {
          this.innerHTML = originalHTML;
        }, 2000);
      }.bind(this));
    });
  </script>
</body>
</html>