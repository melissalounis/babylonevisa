<?php
session_start();

// Connexion BDD
require_once __DIR__ . '../../../config.php';
try {
    

    // Récupérer l'ID de la demande depuis l'URL
    $demande_id = $_GET['id'] ?? 0;
    
    if (!$demande_id) {
        die("ID de demande invalide");
    }

    // Récupérer les informations de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_campus_france WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée");
    }

    // Récupérer les fichiers joints
    $stmt_files = $pdo->prepare("SELECT * FROM demandes_campus_france_fichiers WHERE demande_id = ?");
    $stmt_files->execute([$demande_id]);
    $fichiers = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

    // Décoder les relevés par année
    $releves_annees = json_decode($demande['releves_annees'] ?? '{}', true);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation - Campus France</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4b0082;
      --secondary-color: #8a2be2;
      --success-color: #28a745;
      --warning-color: #ffc107;
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
      max-width: 1000px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 30px;
      text-align: center;
    }
    
    .success-icon {
      font-size: 4rem;
      margin-bottom: 20px;
      color: var(--success-color);
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 2rem;
    }
    
    .reference-number {
      background: rgba(255, 255, 255, 0.2);
      padding: 15px;
      border-radius: var(--border-radius);
      margin: 20px 0;
      font-size: 1.2rem;
      font-weight: bold;
    }
    
    .content {
      padding: 30px;
    }
    
    .info-section {
      margin-bottom: 30px;
      padding: 25px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: var(--light-gray);
    }
    
    .info-section h3 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--border-color);
      display: flex;
      align-items: center;
    }
    
    .info-section h3 i {
      margin-right: 10px;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
    }
    
    .info-item {
      margin-bottom: 15px;
    }
    
    .info-label {
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 5px;
    }
    
    .info-value {
      padding: 8px 12px;
      background: white;
      border-radius: var(--border-radius);
      border-left: 4px solid var(--secondary-color);
    }
    
    .files-list {
      list-style: none;
    }
    
    .files-list li {
      padding: 10px;
      background: white;
      margin-bottom: 5px;
      border-radius: var(--border-radius);
      border-left: 4px solid var(--info-color);
    }
    
    .files-list li i {
      margin-right: 10px;
      color: var(--secondary-color);
    }
    
    .next-steps {
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      padding: 25px;
      border-radius: var(--border-radius);
      margin: 30px 0;
    }
    
    .next-steps h3 {
      color: var(--primary-color);
      margin-bottom: 15px;
    }
    
    .next-steps ol {
      margin-left: 20px;
    }
    
    .next-steps li {
      margin-bottom: 10px;
    }
    
    .actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
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
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    footer {
      text-align: center;
      padding: 20px;
      background: var(--light-gray);
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-left: 10px;
    }
    
    .status-en_attente {
      background: #fff3cd;
      color: #856404;
    }
    
    @media (max-width: 768px) {
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <div class="success-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h1><i class="fas fa-graduation-cap"></i> Demande Campus France Enregistrée</h1>
    <p>Votre demande a été soumise avec succès</p>
    
    <div class="reference-number">
      <i class="fas fa-hashtag"></i>
      Référence : CF-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>
    </div>
    
    <div class="status-badge status-<?php echo $demande['statut']; ?>">
      Statut : <?php 
        switch($demande['statut']) {
            case 'en_attente': echo 'En attente de traitement'; break;
            case 'en_traitement': echo 'En cours de traitement'; break;
            case 'approuve': echo 'Approuvée'; break;
            case 'refuse': echo 'Refusée'; break;
            default: echo $demande['statut'];
        }
      ?>
    </div>
  </header>

  <div class="content">
    <!-- Informations du projet d'études -->
    <div class="info-section">
      <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Niveau d'études visé</div>
          <div class="info-value">
            <?php 
              $niveaux = [
                'licence1' => 'Licence 1ère année',
                'licence2' => 'Licence 2ème année',
                'licence3' => 'Licence 3ème année',
                'master1' => 'Master 1ère année',
                'master2' => 'Master 2ème année',
                'doctorat' => 'Doctorat',
                'bts' => 'BTS',
                'dut' => 'DUT',
                'inge' => 'École d\'ingénieurs',
                'commerce' => 'École de commerce'
              ];
              echo $niveaux[$demande['niveau_etudes']] ?? $demande['niveau_etudes'];
            ?>
          </div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Domaine d'études</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Formation</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['nom_formation']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Établissement</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['etablissement']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Date de début</div>
          <div class="info-value"><?php echo date('d/m/Y', strtotime($demande['date_debut'])); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Durée des études</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['duree_etudes']); ?></div>
        </div>
      </div>
    </div>

    <!-- Informations personnelles -->
    <div class="info-section">
      <h3><i class="fas fa-user-graduate"></i> Informations personnelles</h3>
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Nom complet</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Date de naissance</div>
          <div class="info-value"><?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Lieu de naissance</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['lieu_naissance']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Nationalité</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['nationalite']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Téléphone</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['telephone']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Email</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['email']); ?></div>
        </div>
      </div>
    </div>

    <!-- Parcours académique -->
    <div class="info-section">
      <h3><i class="fas fa-university"></i> Parcours académique</h3>
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Dernier diplôme obtenu</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['dernier_diplome']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Établissement d'origine</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['etablissement_origine']); ?></div>
        </div>
        
        <?php if (!empty($demande['moyenne_derniere_annee'])): ?>
        <div class="info-item">
          <div class="info-label">Moyenne dernière année</div>
          <div class="info-value"><?php echo htmlspecialchars($demande['moyenne_derniere_annee']); ?></div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($demande['niveau_francais'])): ?>
        <div class="info-item">
          <div class="info-label">Niveau de français</div>
          <div class="info-value">
            <?php 
              $niveaux_fr = [
                'debutant' => 'Débutant (A1-A2)',
                'intermediaire' => 'Intermédiaire (B1-B2)',
                'avance' => 'Avancé (C1-C2)',
                'bilingue' => 'Bilingue'
              ];
              echo $niveaux_fr[$demande['niveau_francais']] ?? $demande['niveau_francais'];
            ?>
          </div>
        </div>
        <?php endif; ?>
        
        <?php if ($demande['tests_francais'] !== 'non'): ?>
        <div class="info-item">
          <div class="info-label">Test de français</div>
          <div class="info-value">
            <?php 
              $tests = [
                'tcf' => 'TCF',
                'delf' => 'DELF',
                'dalf' => 'DALF',
                'autre' => 'Autre'
              ];
              echo ($tests[$demande['tests_francais']] ?? $demande['tests_francais']) . 
                   (!empty($demande['score_test']) ? ' - ' . htmlspecialchars($demande['score_test']) : '');
            ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Fichiers joints -->
    <div class="info-section">
      <h3><i class="fas fa-paperclip"></i> Documents fournis</h3>
      <?php if ($fichiers): ?>
        <ul class="files-list">
          <?php foreach ($fichiers as $fichier): ?>
            <li>
              <i class="fas fa-file-<?php 
                $extension = pathinfo($fichier['chemin_fichier'], PATHINFO_EXTENSION);
                echo in_array($extension, ['pdf']) ? 'pdf' : 
                     (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'alt');
              ?>"></i>
              
              <?php 
                $types_fichiers = [
                  'copie_passeport' => 'Copie du passeport',
                  'diplomes' => 'Diplômes',
                  'releves_notes' => 'Relevés de notes globaux',
                  'lettre_motivation' => 'Lettre de motivation',
                  'cv' => 'Curriculum Vitae',
                  'attestation_francais' => 'Attestation de français',
                  'attestation_acceptation' => 'Attestation d\'acceptation',
                  'certificat_scolarite' => 'Certificat de scolarité'
                ];
                
                for ($i = 1; $i <= 5; $i++) {
                  $types_fichiers["releve_annee_$i"] = "Relevé de notes année $i";
                }
                
                echo $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
              ?>
              
              <small>(<?php echo date('d/m/Y H:i', strtotime($fichier['date_upload'])); ?>)</small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Aucun document joint pour le moment.</p>
      <?php endif; ?>
    </div>

    <!-- Prochaines étapes -->
    <div class="next-steps">
      <h3><i class="fas fa-list-ol"></i> Prochaines étapes</h3>
      <ol>
        <li><strong>Vérification du dossier</strong> : Notre équipe va examiner votre demande sous 48 heures</li>
        <li><strong>Complément d'information</strong> : Si nécessaire, nous vous contacterons pour des documents supplémentaires</li>
        <li><strong>Validation Campus France</strong> : Votre dossier sera transmis à Campus France pour évaluation</li>
        <li><strong>Suivi en temps réel</strong> : Vous recevrez des notifications sur l'avancement de votre dossier</li>
        <li><strong>Rendez-vous consulaire</strong> : Une fois pré-approuvé, vous serez convoqué pour un entretien</li>
      </ol>
    </div>

  

    <!-- Informations de contact -->
    <div class="info-section">
      <h3><i class="fas fa-headset"></i> Assistance</h3>
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Email de contact</div>
          <div class="info-value">babylone.service15@gmail.com</div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Téléphone</div>
          <div class="info-value">0554 31 00 47</div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Horaires d'ouverture</div>
          <div class="info-value">sam-jeudi : 8h30h-17h</div>
        </div>
        
        <div class="info-item">
          <div class="info-label">Référence du dossier</div>
          <div class="info-value">CF-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></div>
        </div>
      </div>
    </div>
  </div>
  
  <footer>
    <p>© 2023 Campus France - Babylone Service. Tous droits réservés.</p>
    <p>Date de soumission : <?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?></p>
  </footer>
</div>

<script>
// Fonction pour imprimer la page
function imprimerConfirmation() {
    window.print();
}

// Fonction pour sauvegarder en PDF
function sauvegarderPDF() {
    alert('Fonction de sauvegarde PDF bientôt disponible');
}

// Ajout des événements après le chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter un timer pour redirection automatique vers l'espace étudiant après 30 secondes
    setTimeout(function() {
        console.log('Redirection automatique vers l\'espace étudiant dans 30 secondes...');
    }, 30000);
});
</script>
</body>
</html>