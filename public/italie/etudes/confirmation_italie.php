<?php
session_start();

// Vérifier que l'ID de demande est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formulaire_italie.php");
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
    $stmt = $pdo->prepare("SELECT * FROM demandes_italie WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée.");
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation de Demande - Italie</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #008C45;
      --secondary-color: #CD212A;
      --accent-color: #F4F5F0;
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
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    header {
      background: linear-gradient(135deg, #008C45, #CD212A);
      color: white;
      padding: 30px;
    }
    
    .success-icon {
      font-size: 4rem;
      margin-bottom: 20px;
    }
    
    .content {
      padding: 40px;
    }
    
    .info-box {
      background: var(--accent-color);
      padding: 20px;
      border-radius: 8px;
      margin: 20px 0;
      text-align: left;
    }
    
    .next-steps {
      text-align: left;
      margin: 30px 0;
    }
    
    .next-steps ul {
      list-style-type: none;
      padding-left: 20px;
    }
    
    .next-steps li {
      margin-bottom: 10px;
      position: relative;
    }
    
    .next-steps li:before {
      content: "✓";
      color: var(--success-color);
      font-weight: bold;
      position: absolute;
      left: -20px;
    }
    
    .btn {
      display: inline-block;
      background: linear-gradient(to right, #008C45, #CD212A);
      color: white;
      padding: 12px 25px;
      text-decoration: none;
      border-radius: 5px;
      margin: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    footer {
      padding: 20px;
      background: var(--accent-color);
      color: #666;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1>Demande Soumise avec Succès !</h1>
      <p>Votre demande d'études en Italie a été enregistrée</p>
    </header>
    
    <div class="content">
      <div class="info-box">
        <h3><i class="fas fa-info-circle"></i> Informations de votre demande</h3>
        <p><strong>Référence :</strong> IT<?php echo str_pad($demande_id, 6, '0', STR_PAD_LEFT); ?></p>
        <p><strong>Nom :</strong> <?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></p>
        <p><strong>Niveau :</strong> <?php echo htmlspecialchars($demande['niveau_etudes']); ?></p>
        <p><strong>Établissement :</strong> <?php echo htmlspecialchars($demande['etablissement']); ?></p>
        <p><strong>Date de soumission :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
      </div>
      
      <div class="next-steps">
        <h3><i class="fas fa-list-alt"></i> Prochaines étapes</h3>
        <ul>
          <li>Votre demande est en cours de traitement</li>
          <li>Vous recevrez un email de confirmation sous 24 heures</li>
          <li>Notre équipe vérifiera vos documents</li>
          <li>Un conseiller vous contactera pour la suite de la procédure</li>
          <li>Délai moyen de traitement : 3 à 5 jours ouvrables</li>
        </ul>
      </div>
      
      <div>
        <a href="formulaire_italie.php" class="btn">
          <i class="fas fa-plus"></i> Nouvelle demande
        </a>
        <a href="espace_utilisateur.php" class="btn">
          <i class="fas fa-user"></i> Mon espace
        </a>
        <a href="index.php" class="btn">
          <i class="fas fa-home"></i> Accueil
        </a>
      </div>
    </div>
    
    <footer>
      <p>Pour toute question, contactez-nous à assistance@babylone-service.com</p>
      <p>© 2024 Service d'Études à l'Étranger</p>
    </footer>
  </div>
</body>
</html>