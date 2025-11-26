<?php
// Connexion à la base de données
require_once __DIR__ . '/../../../config.php';

class ParcoursupManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Vérifier si les colonnes existent, sinon les créer
    private function verifierStructureTable() {
        $columns_requises = [
            'date_creation', 'date_modification', 'statut', 'type_piece_identite',
            'num_piece_identite', 'date_delivrance_piece', 'date_expiration_piece',
            'a_garant_france', 'nom_pere', 'prenom_pere', 'profession_pere', 'employeur_pere', 'csp_pere',
            'nom_mere', 'prenom_mere', 'profession_mere', 'employeur_mere', 'csp_mere',
            'nom_garant', 'prenom_garant', 'adresse_garant', 'telephone_garant', 'email_garant', 'lien_parente_garant',
            'tests_francais', 'score_test', 'test_anglais', 'score_anglais', 'boite_pastel', 'email_pastel'
        ];
        
        foreach ($columns_requises as $colonne) {
            try {
                $stmt = $this->db->prepare("SELECT $colonne FROM demandes_parcoursup LIMIT 1");
                $stmt->execute();
            } catch (Exception $e) {
                // La colonne n'existe pas, on la crée
                $this->ajouterColonne($colonne);
            }
        }
    }
    
    private function ajouterColonne($nom_colonne) {
        $type = 'VARCHAR(255)';
        
        if (in_array($nom_colonne, ['date_creation', 'date_modification'])) {
            $type = 'DATETIME';
        } elseif (in_array($nom_colonne, ['date_delivrance_piece', 'date_expiration_piece', 'date_naissance'])) {
            $type = 'DATE';
        } elseif (in_array($nom_colonne, ['statut'])) {
            $type = 'VARCHAR(20) DEFAULT "en_attente"';
        } elseif ($nom_colonne === 'date_creation') {
            $type = 'DATETIME DEFAULT CURRENT_TIMESTAMP';
        }
        
        $sql = "ALTER TABLE demandes_parcoursup ADD COLUMN $nom_colonne $type";
        $this->db->exec($sql);
    }
    
    // Obtenir l'ID de l'utilisateur connecté (à adapter selon votre système d'authentification)
    private function getUserId() {
        // Si vous avez une session utilisateur
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        
        // Si vous voulez permettre des demandes sans utilisateur connecté
        // return null;
        
        // Pour le moment, on retourne 1 comme utilisateur par défaut
        // À remplacer par votre logique d'authentification
        return 1;
    }
    
    // Vérifier si user_id existe dans la table users
    private function verifierUserId($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Sauvegarder une demande
    public function sauvegarderDemande($data) {
        try {
            // Vérifier la structure de la table
            $this->verifierStructureTable();
            
            // Obtenir l'ID utilisateur
            $user_id = $this->getUserId();
            
            // Vérifier si user_id existe
            if (!$this->verifierUserId($user_id)) {
                // Si l'utilisateur n'existe pas, on supprime la contrainte ou on utilise une valeur par défaut
                // Option 1: Désactiver temporairement les contraintes
                $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
            }
            
            // Commencer une transaction
            $this->db->beginTransaction();
            
            // Construction dynamique de la requête SQL
            $colonnes = ['user_id'];
            $placeholders = ['?'];
            $valeurs = [$user_id];
            
            // Liste des champs possibles
            $champs = [
                'niveau_etudes', 'domaine_etudes', 'nom', 'prenom', 'date_naissance', 'lieu_naissance',
                'nationalite', 'adresse', 'telephone', 'email', 'situation_familiale', 'profession_candidat',
                'categorie_socio_pro', 'type_piece_identite', 'num_piece_identite', 'date_delivrance_piece',
                'date_expiration_piece', 'a_garant_france', 'nom_pere', 'prenom_pere', 'profession_pere',
                'employeur_pere', 'csp_pere', 'nom_mere', 'prenom_mere', 'profession_mere', 'employeur_mere',
                'csp_mere', 'nom_garant', 'prenom_garant', 'adresse_garant', 'telephone_garant',
                'email_garant', 'lien_parente_garant', 'tests_francais', 'score_test', 'test_anglais',
                'score_anglais', 'boite_pastel', 'email_pastel'
            ];
            
            foreach ($champs as $champ) {
                if (isset($data[$champ]) && $data[$champ] !== '') {
                    $colonnes[] = $champ;
                    $placeholders[] = '?';
                    $valeurs[] = $data[$champ];
                }
            }
            
            // Ajouter les champs système
            $colonnes[] = 'statut';
            $placeholders[] = '?';
            $valeurs[] = 'en_attente';
            
            $colonnes[] = 'date_creation';
            $placeholders[] = 'NOW()';
            
            $sql = "INSERT INTO demandes_parcoursup (" . implode(', ', $colonnes) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valeurs);
            
            $demande_id = $this->db->lastInsertId();
            
            // Gérer les fichiers uploadés
            $this->sauvegarderFichiers($demande_id, $_FILES);
            
            // Réactiver les contraintes si on les a désactivées
            if (!$this->verifierUserId($user_id)) {
                $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return $demande_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            
            // Réactiver les contraintes en cas d'erreur
            $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
            
            throw $e;
        }
    }
    
    // Les autres méthodes restent inchangées...
    private function sauvegarderFichiers($demande_id, $files) {
        $dossier_upload = __DIR__ . '/../uploads/parcoursup/' . $demande_id . '/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($dossier_upload)) {
            mkdir($dossier_upload, 0777, true);
        }
        
        $fichiers_a_sauvegarder = [
            'photo_identite' => 'photo_identite',
            'copie_piece_identite' => 'piece_identite',
            'lettre_motivation' => 'lettre_motivation',
            'cv' => 'cv',
            'certificat_scolarite' => 'certificat_scolarite',
            'attestation_francais' => 'attestation_francais',
            'attestation_anglais' => 'attestation_anglais',
            'piece_identite_garant' => 'piece_identite_garant'
        ];
        
        foreach ($fichiers_a_sauvegarder as $input_name => $type_fichier) {
            if (isset($files[$input_name]) && $files[$input_name]['error'] === UPLOAD_ERR_OK) {
                $nom_fichier = $this->genererNomFichier($files[$input_name]['name'], $type_fichier);
                $chemin_complet = $dossier_upload . $nom_fichier;
                
                if (move_uploaded_file($files[$input_name]['tmp_name'], $chemin_complet)) {
                    // Vérifier si la table fichiers_demandes existe
                    $this->creerTableFichiersSiNecessaire();
                    
                    // Sauvegarder en base
                    $sql = "INSERT INTO fichiers_demandes (demande_id, type_fichier, nom_fichier, chemin) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$demande_id, $type_fichier, $nom_fichier, $chemin_complet]);
                }
            }
        }
        
        // Gérer les relevés de notes
        for ($i = 1; $i <= 6; $i++) {
            $input_name = "releve_annee_$i";
            if (isset($files[$input_name]) && $files[$input_name]['error'] === UPLOAD_ERR_OK) {
                $nom_fichier = $this->genererNomFichier($files[$input_name]['name'], "releve_notes_$i");
                $chemin_complet = $dossier_upload . $nom_fichier;
                
                if (move_uploaded_file($files[$input_name]['tmp_name'], $chemin_complet)) {
                    $sql = "INSERT INTO fichiers_demandes (demande_id, type_fichier, nom_fichier, chemin) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$demande_id, "releve_notes_$i", $nom_fichier, $chemin_complet]);
                }
            }
        }
        
        // Gérer les autres documents
        for ($i = 1; $i <= 5; $i++) {
            $input_name = "fichier_document_$i";
            if (isset($files[$input_name]) && $files[$input_name]['error'] === UPLOAD_ERR_OK) {
                $type_doc = $_POST["type_document_$i"] ?? "autre";
                $nom_fichier = $this->genererNomFichier($files[$input_name]['name'], $type_doc . "_$i");
                $chemin_complet = $dossier_upload . $nom_fichier;
                
                if (move_uploaded_file($files[$input_name]['tmp_name'], $chemin_complet)) {
                    $sql = "INSERT INTO fichiers_demandes (demande_id, type_fichier, nom_fichier, chemin) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$demande_id, $type_doc . "_$i", $nom_fichier, $chemin_complet]);
                }
            }
        }
    }
    
    private function creerTableFichiersSiNecessaire() {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM fichiers_demandes LIMIT 1");
            $stmt->execute();
        } catch (Exception $e) {
            // La table n'existe pas, on la crée
            $sql = "CREATE TABLE fichiers_demandes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                demande_id INT NOT NULL,
                type_fichier VARCHAR(100) NOT NULL,
                nom_fichier VARCHAR(255) NOT NULL,
                chemin VARCHAR(500) NOT NULL,
                date_upload DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            $this->db->exec($sql);
        }
    }
    
    private function genererNomFichier($nom_original, $type) {
        $extension = pathinfo($nom_original, PATHINFO_EXTENSION);
        return $type . '_' . uniqid() . '.' . $extension;
    }
    
    // Récupérer toutes les demandes
    public function getDemandes($filtre_statut = null) {
        $sql = "SELECT d.*, u.username, u.email as user_email 
                FROM demandes_parcoursup d 
                LEFT JOIN users u ON d.user_id = u.id";
        
        if ($filtre_statut) {
            $sql .= " WHERE d.statut = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$filtre_statut]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupérer une demande spécifique
    public function getDemande($id) {
        $sql = "SELECT d.*, u.username, u.email as user_email 
                FROM demandes_parcoursup d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Récupérer les fichiers d'une demande
    public function getFichiersDemande($demande_id) {
        $this->creerTableFichiersSiNecessaire();
        
        $sql = "SELECT * FROM fichiers_demandes WHERE demande_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$demande_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mettre à jour le statut d'une demande
    public function updateStatut($id, $statut) {
        $sql = "UPDATE demandes_parcoursup SET statut = ?, date_modification = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$statut, $id]);
    }
    
    // Supprimer une demande
    public function supprimerDemande($id) {
        try {
            $this->db->beginTransaction();
            
            // Supprimer les fichiers physiques
            $fichiers = $this->getFichiersDemande($id);
            foreach ($fichiers as $fichier) {
                if (file_exists($fichier['chemin'])) {
                    unlink($fichier['chemin']);
                }
            }
            
            // Supprimer le dossier
            $dossier = dirname($fichiers[0]['chemin'] ?? '');
            if (is_dir($dossier)) {
                rmdir($dossier);
            }
            
            // Supprimer les enregistrements en base
            $this->creerTableFichiersSiNecessaire();
            $sql1 = "DELETE FROM fichiers_demandes WHERE demande_id = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$id]);
            
            $sql2 = "DELETE FROM demandes_parcoursup WHERE id = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$id]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}

// Initialisation
$manager = new ParcoursupManager($pdo);
$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $demande_id = $manager->sauvegarderDemande($_POST);
        $success = "Votre demande a été enregistrée avec succès (ID: $demande_id)";
        
        // Réinitialiser les données du formulaire après succès
        $_POST = array();
        $_FILES = array();
    } catch (Exception $e) {
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire Parcoursup</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #003366;
      --secondary-color: #0055aa;
      --accent-color: #ff6b35;
      --light-blue: #e8f2ff;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --border-color: #dbe4ee;
      --success-color: #28a745;
      --error-color: #dc3545;
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
      background: linear-gradient(135deg, #4b0082, #8a2be2);
      color: white;
      padding: 25px 30px;
      text-align: center;
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 1.8rem;
    }
    
    header p {
      opacity: 0.9;
    }
    
    .alert {
      padding: 15px;
      margin: 20px;
      border-radius: 5px;
      border: 1px solid;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .alert-error {
      background: #ffebee;
      color: #c62828;
      border-color: #ffcdd2;
    }
    
    .alert-success {
      background: #e8f5e9;
      color: #2e7d32;
      border-color: #c8e6c9;
    }
    
    .alert-close {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      color: inherit;
      opacity: 0.7;
    }
    
    .alert-close:hover {
      opacity: 1;
    }
    
    .form-content {
      padding: 30px;
    }
    
    .form-section {
      margin-bottom: 30px;
      padding: 25px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: var(--light-gray);
    }
    
    .form-section h3 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-blue);
      display: flex;
      align-items: center;
    }
    
    .form-section h3 i {
      margin-right: 10px;
      color: #4b0082;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--dark-text);
    }
    
    .required::after {
      content: " *";
      color: var(--error-color);
    }
    
    .optional::after {
      content: " (Optionnel)";
      color: #666;
      font-weight: normal;
    }
    
    input, select, textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      font-size: 1rem;
      transition: var(--transition);
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 3px rgba(0, 85, 170, 0.2);
    }
    
    .file-input {
      border: 2px dashed var(--border-color);
      padding: 15px;
      background: var(--light-blue);
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .file-input:hover {
      border-color: var(--secondary-color);
    }
    
    .file-hint {
      font-size: 0.85rem;
      color: #666;
      margin-top: 5px;
      display: block;
    }
    
    .btn-submit {
      background: linear-gradient(to right, #4b0082, #8a2be2);
      color: white;
      padding: 15px 30px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn-submit:hover {
      background: linear-gradient(to right, #3a0066, #6a1cb2);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    .btn-submit i {
      margin-right: 10px;
    }
    
    .hidden {
      display: none;
    }
    
    .form-row {
      display: flex;
      gap: 20px;
    }
    
    .form-row .form-group {
      flex: 1;
    }
    
    footer {
      text-align: center;
      padding: 20px;
      background: var(--light-blue);
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    .conditional-section {
      border-left: 4px solid #8a2be2;
      padding-left: 15px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    
    .annee-section {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
    }
    
    .document-section {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
    }
    
    .test-rdv-link {
      display: inline-block;
      background: var(--accent-color);
      color: white;
      padding: 10px 20px;
      border-radius: var(--border-radius);
      text-decoration: none;
      margin-top: 10px;
      font-weight: 600;
      transition: var(--transition);
    }
    
    .test-rdv-link:hover {
      background: #e55a2b;
      transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Formulaire Parcoursup</h1>
    <p>Demande d'inscription pour études en France</p>
  </header>

  <!-- Afficher les messages d'erreur/succès -->
  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <div>
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
      <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success">
      <div>
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
      </div>
      <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
    </div>
  <?php endif; ?>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
        <div class="form-group">
          <label class="required">Pays d'études</label>
          <input type="text" name="pays_etudes" required value="France" readonly>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Niveau d'études visé</label>
            <select id="niveau_etudes" name="niveau_etudes" required onchange="toggleRelevesAnnees(); toggleCertificatScolarite();">
              <option value="">-- Sélectionnez --</option>
              <option value="licence1" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'licence1' ? 'selected' : ''; ?>>Licence 1ère année</option>
              <option value="licence2" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'licence2' ? 'selected' : ''; ?>>Licence 2ème année</option>
              <option value="licence3" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'licence3' ? 'selected' : ''; ?>>Licence 3ème année</option>
              <option value="master1" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'master1' ? 'selected' : ''; ?>>Master 1ère année</option>
              <option value="master2" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'master2' ? 'selected' : ''; ?>>Master 2ème année</option>
              <option value="master_termine" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'master_termine' ? 'selected' : ''; ?>>Master terminé (diplômé)</option>
              <option value="doctorat" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
              <option value="bts" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'bts' ? 'selected' : ''; ?>>BTS</option>
              <option value="dut" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'dut' ? 'selected' : ''; ?>>DUT</option>
              <option value="inge" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'inge' ? 'selected' : ''; ?>>École d'ingénieurs</option>
              <option value="commerce" <?php echo isset($_POST['niveau_etudes']) && $_POST['niveau_etudes'] == 'commerce' ? 'selected' : ''; ?>>École de commerce</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required placeholder="Informatique, Droit, Médecine..." value="<?php echo isset($_POST['domaine_etudes']) ? htmlspecialchars($_POST['domaine_etudes']) : ''; ?>">
          </div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user-graduate"></i> Informations personnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom</label>
            <input type="text" name="nom" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Prénom</label>
            <input type="text" name="prenom" required value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de naissance</label>
            <input type="date" name="date_naissance" required value="<?php echo isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" required value="<?php echo isset($_POST['lieu_naissance']) ? htmlspecialchars($_POST['lieu_naissance']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Nationalité</label>
          <input type="text" name="nationalite" required value="<?php echo isset($_POST['nationalite']) ? htmlspecialchars($_POST['nationalite']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Adresse complète</label>
          <textarea name="adresse" required rows="3"><?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Téléphone</label>
            <input type="tel" name="telephone" required value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Email</label>
            <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Photo d'identité</label>
          <input type="file" name="photo_identite" class="file-input" accept=".jpg,.jpeg,.png" required>
          <span class="file-hint">Photo format passeport (jpg, png - max 2MB)</span>
        </div>
      </div>

      <!-- Pièce d'identité -->
      <div class="form-section">
        <h3><i class="fas fa-id-card"></i> Pièce d'identité</h3>
        <div class="form-group">
          <label class="required">Type de pièce d'identité</label>
          <select name="type_piece_identite" required>
            <option value="">-- Sélectionnez --</option>
            <option value="cni" <?php echo isset($_POST['type_piece_identite']) && $_POST['type_piece_identite'] == 'cni' ? 'selected' : ''; ?>>Carte nationale d'identité</option>
            <option value="passeport" <?php echo isset($_POST['type_piece_identite']) && $_POST['type_piece_identite'] == 'passeport' ? 'selected' : ''; ?>>Passeport</option>
            <option value="titre_sejour" <?php echo isset($_POST['type_piece_identite']) && $_POST['type_piece_identite'] == 'titre_sejour' ? 'selected' : ''; ?>>Titre de séjour</option>
          </select>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Numéro de la pièce</label>
            <input type="text" name="num_piece_identite" required value="<?php echo isset($_POST['num_piece_identite']) ? htmlspecialchars($_POST['num_piece_identite']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Date de délivrance</label>
            <input type="date" name="date_delivrance_piece" required value="<?php echo isset($_POST['date_delivrance_piece']) ? htmlspecialchars($_POST['date_delivrance_piece']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Date d'expiration</label>
            <input type="date" name="date_expiration_piece" required value="<?php echo isset($_POST['date_expiration_piece']) ? htmlspecialchars($_POST['date_expiration_piece']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Copie de la pièce d'identité</label>
          <input type="file" name="copie_piece_identite" class="file-input" accept=".pdf,.jpg,.png" required>
          <span class="file-hint">Recto-verso pour les cartes d'identité (max 5MB)</span>
        </div>
      </div>

      <!-- Situation familiale et professionnelle -->
      <div class="form-section">
        <h3><i class="fas fa-users"></i> Situation familiale et professionnelle</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Situation familiale</label>
            <select name="situation_familiale" required>
              <option value="">-- Sélectionnez --</option>
              <option value="celibataire" <?php echo isset($_POST['situation_familiale']) && $_POST['situation_familiale'] == 'celibataire' ? 'selected' : ''; ?>>Célibataire</option>
              <option value="marie" <?php echo isset($_POST['situation_familiale']) && $_POST['situation_familiale'] == 'marie' ? 'selected' : ''; ?>>Marié(e)</option>
              <option value="pacse" <?php echo isset($_POST['situation_familiale']) && $_POST['situation_familiale'] == 'pacse' ? 'selected' : ''; ?>>Pacsé(e)</option>
              <option value="divorce" <?php echo isset($_POST['situation_familiale']) && $_POST['situation_familiale'] == 'divorce' ? 'selected' : ''; ?>>Divorcé(e)</option>
              <option value="veuf" <?php echo isset($_POST['situation_familiale']) && $_POST['situation_familiale'] == 'veuf' ? 'selected' : ''; ?>>Veuf/Veuve</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Profession</label>
            <input type="text" name="profession_candidat" required value="<?php echo isset($_POST['profession_candidat']) ? htmlspecialchars($_POST['profession_candidat']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Catégorie socio-professionnelle</label>
            <select name="categorie_socio_pro" required>
              <option value="">-- Sélectionnez --</option>
              <option value="agriculteur" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'agriculteur' ? 'selected' : ''; ?>>Agriculteur</option>
              <option value="artisan" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'artisan' ? 'selected' : ''; ?>>Artisan, commerçant</option>
              <option value="cadre" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'cadre' ? 'selected' : ''; ?>>Cadre, profession intellectuelle</option>
              <option value="prof_intermediaire" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'prof_intermediaire' ? 'selected' : ''; ?>>Profession intermédiaire</option>
              <option value="employe" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'employe' ? 'selected' : ''; ?>>Employé</option>
              <option value="ouvrier" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'ouvrier' ? 'selected' : ''; ?>>Ouvrier</option>
              <option value="retraite" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'retraite' ? 'selected' : ''; ?>>Retraité</option>
              <option value="sans_activite" <?php echo isset($_POST['categorie_socio_pro']) && $_POST['categorie_socio_pro'] == 'sans_activite' ? 'selected' : ''; ?>>Sans activité professionnelle</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Informations des parents -->
      <div class="form-section">
        <h3><i class="fas fa-user-friends"></i> Informations des parents</h3>
        
        <h4>Père</h4>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom</label>
            <input type="text" name="nom_pere" required value="<?php echo isset($_POST['nom_pere']) ? htmlspecialchars($_POST['nom_pere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Prénom</label>
            <input type="text" name="prenom_pere" required value="<?php echo isset($_POST['prenom_pere']) ? htmlspecialchars($_POST['prenom_pere']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Profession</label>
            <input type="text" name="profession_pere" required value="<?php echo isset($_POST['profession_pere']) ? htmlspecialchars($_POST['profession_pere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Employeur</label>
            <input type="text" name="employeur_pere" required value="<?php echo isset($_POST['employeur_pere']) ? htmlspecialchars($_POST['employeur_pere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Catégorie socio-professionnelle</label>
            <select name="csp_pere" required>
              <option value="">-- Sélectionnez --</option>
              <option value="agriculteur" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'agriculteur' ? 'selected' : ''; ?>>Agriculteur</option>
              <option value="artisan" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'artisan' ? 'selected' : ''; ?>>Artisan, commerçant</option>
              <option value="cadre" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'cadre' ? 'selected' : ''; ?>>Cadre, profession intellectuelle</option>
              <option value="prof_intermediaire" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'prof_intermediaire' ? 'selected' : ''; ?>>Profession intermédiaire</option>
              <option value="employe" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'employe' ? 'selected' : ''; ?>>Employé</option>
              <option value="ouvrier" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'ouvrier' ? 'selected' : ''; ?>>Ouvrier</option>
              <option value="retraite" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'retraite' ? 'selected' : ''; ?>>Retraité</option>
              <option value="sans_activite" <?php echo isset($_POST['csp_pere']) && $_POST['csp_pere'] == 'sans_activite' ? 'selected' : ''; ?>>Sans activité professionnelle</option>
            </select>
          </div>
        </div>
        
        <h4>Mère</h4>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom</label>
            <input type="text" name="nom_mere" required value="<?php echo isset($_POST['nom_mere']) ? htmlspecialchars($_POST['nom_mere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Prénom</label>
            <input type="text" name="prenom_mere" required value="<?php echo isset($_POST['prenom_mere']) ? htmlspecialchars($_POST['prenom_mere']) : ''; ?>">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Profession</label>
            <input type="text" name="profession_mere" required value="<?php echo isset($_POST['profession_mere']) ? htmlspecialchars($_POST['profession_mere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Employeur</label>
            <input type="text" name="employeur_mere" required value="<?php echo isset($_POST['employeur_mere']) ? htmlspecialchars($_POST['employeur_mere']) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="required">Catégorie socio-professionnelle</label>
            <select name="csp_mere" required>
              <option value="">-- Sélectionnez --</option>
              <option value="agriculteur" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'agriculteur' ? 'selected' : ''; ?>>Agriculteur</option>
              <option value="artisan" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'artisan' ? 'selected' : ''; ?>>Artisan, commerçant</option>
              <option value="cadre" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'cadre' ? 'selected' : ''; ?>>Cadre, profession intellectuelle</option>
              <option value="prof_intermediaire" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'prof_intermediaire' ? 'selected' : ''; ?>>Profession intermédiaire</option>
              <option value="employe" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'employe' ? 'selected' : ''; ?>>Employé</option>
              <option value="ouvrier" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'ouvrier' ? 'selected' : ''; ?>>Ouvrier</option>
              <option value="retraite" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'retraite' ? 'selected' : ''; ?>>Retraité</option>
              <option value="sans_activite" <?php echo isset($_POST['csp_mere']) && $_POST['csp_mere'] == 'sans_activite' ? 'selected' : ''; ?>>Sans activité professionnelle</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Garant en France -->
      <div class="form-section">
        <h3><i class="fas fa-hand-holding-heart"></i> Garant en France</h3>
        
        <div class="form-group">
          <label class="required">Avez-vous un garant en France ?</label>
          <select id="a_garant_france" name="a_garant_france" required onchange="toggleGarantSection()">
            <option value="">-- Sélectionnez --</option>
            <option value="non" <?php echo isset($_POST['a_garant_france']) && $_POST['a_garant_france'] == 'non' ? 'selected' : ''; ?>>Non</option>
            <option value="oui" <?php echo isset($_POST['a_garant_france']) && $_POST['a_garant_france'] == 'oui' ? 'selected' : ''; ?>>Oui</option>
          </select>
        </div>
        
        <div id="garant_section" class="conditional-section hidden">
          <div class="form-row">
            <div class="form-group">
              <label class="required">Nom du garant</label>
              <input type="text" name="nom_garant" value="<?php echo isset($_POST['nom_garant']) ? htmlspecialchars($_POST['nom_garant']) : ''; ?>">
            </div>
            <div class="form-group">
              <label class="required">Prénom du garant</label>
              <input type="text" name="prenom_garant" value="<?php echo isset($_POST['prenom_garant']) ? htmlspecialchars($_POST['prenom_garant']) : ''; ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label class="required">Adresse complète du garant</label>
            <textarea name="adresse_garant" rows="3"><?php echo isset($_POST['adresse_garant']) ? htmlspecialchars($_POST['adresse_garant']) : ''; ?></textarea>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="required">Téléphone du garant</label>
              <input type="tel" name="telephone_garant" value="<?php echo isset($_POST['telephone_garant']) ? htmlspecialchars($_POST['telephone_garant']) : ''; ?>">
            </div>
            <div class="form-group">
              <label class="required">Email du garant</label>
              <input type="email" name="email_garant" value="<?php echo isset($_POST['email_garant']) ? htmlspecialchars($_POST['email_garant']) : ''; ?>">
            </div>
            <div class="form-group">
              <label class="required">Lien de parenté</label>
              <input type="text" name="lien_parente_garant" placeholder="Ex: Oncle, Tante, Ami..." value="<?php echo isset($_POST['lien_parente_garant']) ? htmlspecialchars($_POST['lien_parente_garant']) : ''; ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label>Pièce d'identité du garant</label>
            <input type="file" name="piece_identite_garant" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie de la pièce d'identité du garant (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Documents de motivation -->
      <div class="form-section">
        <h3><i class="fas fa-file-alt"></i> Documents de motivation</h3>
        <div class="form-group">
          <label class="optional">Lettre de motivation</label>
          <input type="file" name="lettre_motivation" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">Lettre de motivation détaillant votre projet (max 5MB)</span>
        </div>
        
        <div class="form-group">
          <label class="optional">Curriculum Vitae (CV)</label>
          <input type="file" name="cv" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">CV à jour (max 5MB)</span>
        </div>
        
        <div id="certificat_scolarite_section" class="form-group">
          <label class="required">Certificat de scolarité</label>
          <input type="file" id="certificat_scolarite" name="certificat_scolarite" class="file-input" accept=".pdf,.doc,.docx" required>
          <span class="file-hint">Certificat de scolarité (obligatoire pour tous les niveaux sauf Master terminé - max 5MB)</span>
        </div>
      </div>

      <!-- Relevés de notes par année -->
      <div id="releves_annees_section" class="form-section hidden">
        <h3><i class="fas fa-file-alt"></i> Relevés de notes par année (Obligatoire)</h3>
        <div id="releves_annees_container">
          <!-- Les champs seront générés dynamiquement -->
        </div>
      </div>

      <!-- Niveau de français -->
      <div class="form-section">
        <h3><i class="fas fa-language"></i> Niveau de français</h3>
       
        <div class="form-group">
          <label>Avez-vous passé un test de français ?</label>
          <select id="tests_francais" name="tests_francais" onchange="toggleTestFrancais()">
            <option value="non" <?php echo isset($_POST['tests_francais']) && $_POST['tests_francais'] == 'non' ? 'selected' : ''; ?>>Non</option>
            <option value="tcf" <?php echo isset($_POST['tests_francais']) && $_POST['tests_francais'] == 'tcf' ? 'selected' : ''; ?>>TCF</option>
            <option value="delf" <?php echo isset($_POST['tests_francais']) && $_POST['tests_francais'] == 'delf' ? 'selected' : ''; ?>>DELF</option>
            <option value="dalf" <?php echo isset($_POST['tests_francais']) && $_POST['tests_francais'] == 'dalf' ? 'selected' : ''; ?>>DALF</option>
            <option value="autre" <?php echo isset($_POST['tests_francais']) && $_POST['tests_francais'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
          </select>
        </div>
        
        <div id="test_rdv_button" class="hidden">
          <a href="/babylone/public/test_de_langue.php" class="test-rdv-link">
            <i class="fas fa-calendar-check"></i> Demander un rendez-vous test de langue
          </a>
        </div>
        
        <div id="score_test_section" class="conditional-section hidden">
          <div class="form-group">
            <label>Score/Diplôme obtenu</label>
            <input type="text" name="score_test" placeholder="Ex: B2, 450 points..." value="<?php echo isset($_POST['score_test']) ? htmlspecialchars($_POST['score_test']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Attestation de score/diplôme</label>
            <input type="file" name="attestation_francais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Test d'anglais -->
      <div class="form-section">
        <h3><i class="fas fa-globe"></i> Test d'anglais</h3>
       
        <div class="form-group">
          <label>Avez-vous passé un test d'anglais ?</label>
          <select id="test_anglais" name="test_anglais" onchange="toggleTestAnglais()">
            <option value="non" <?php echo isset($_POST['test_anglais']) && $_POST['test_anglais'] == 'non' ? 'selected' : ''; ?>>Non</option>
            <option value="ielts" <?php echo isset($_POST['test_anglais']) && $_POST['test_anglais'] == 'ielts' ? 'selected' : ''; ?>>IELTS</option>
            <option value="toefl" <?php echo isset($_POST['test_anglais']) && $_POST['test_anglais'] == 'toefl' ? 'selected' : ''; ?>>TOEFL</option>
            <option value="toeic" <?php echo isset($_POST['test_anglais']) && $_POST['test_anglais'] == 'toeic' ? 'selected' : ''; ?>>TOEIC</option>
            <option value="autre" <?php echo isset($_POST['test_anglais']) && $_POST['test_anglais'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
          </select>
        </div>
        
        <div id="score_anglais_section" class="conditional-section hidden">
          <div class="form-group">
            <label>Score obtenu</label>
            <input type="text" name="score_anglais" placeholder="Ex: 6.5, 85..." value="<?php echo isset($_POST['score_anglais']) ? htmlspecialchars($_POST['score_anglais']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Attestation de score</label>
            <input type="file" name="attestation_anglais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie de l'attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Boîte Pastel -->
      <div class="form-section">
        <h3><i class="fas fa-envelope"></i> Boîte Pastel</h3>
       
        <div class="form-group">
          <label>Avez-vous déjà une boîte Pastel ?</label>
          <select id="boite_pastel" name="boite_pastel" onchange="toggleBoitePastel()">
            <option value="non" <?php echo isset($_POST['boite_pastel']) && $_POST['boite_pastel'] == 'non' ? 'selected' : ''; ?>>Non</option>
            <option value="oui" <?php echo isset($_POST['boite_pastel']) && $_POST['boite_pastel'] == 'oui' ? 'selected' : ''; ?>>Oui</option>
          </select>
        </div>
        
        <div id="boite_pastel_section" class="conditional-section hidden">
          <div class="form-row">
            <div class="form-group">
              <label>Email Pastel</label>
              <input type="email" name="email_pastel" placeholder="votre.email@pastel.fr" value="<?php echo isset($_POST['email_pastel']) ? htmlspecialchars($_POST['email_pastel']) : ''; ?>">
            </div>
            <div class="form-group">
              <label>Mot de passe Pastel</label>
              <input type="password" name="mdp_pastel" placeholder="Votre mot de passe">
            </div>
          </div>
        </div>
      </div>

      <!-- Autres documents -->
      <div class="form-section">
        <h3><i class="fas fa-folder-open"></i> Autres documents</h3>
       
        <div class="form-group">
          <label>Nombre d'autres documents à ajouter</label>
          <select id="nb_documents" name="nb_documents" onchange="genererChampsDocuments()">
            <option value="0">0 - Aucun document supplémentaire</option>
            <option value="1">1 document</option>
            <option value="2">2 documents</option>
            <option value="3">3 documents</option>
            <option value="4">4 documents</option>
            <option value="5">5 documents</option>
          </select>
        </div>
        
        <div id="autres_documents_container">
          <!-- Les champs seront générés dynamiquement -->
        </div>
      </div>

      <!-- Déclaration -->
      <div class="form-section">
        <h3><i class="fas fa-file-signature"></i> Déclaration</h3>
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="declaration" value="1" required <?php echo isset($_POST['declaration']) ? 'checked' : ''; ?>>
            Je certifie que les informations fournies sont exactes et complètes
          </label>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="conditions" value="1" required <?php echo isset($_POST['conditions']) ? 'checked' : ''; ?>>
            J'accepte les conditions de traitement de mes données personnelles
          </label>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="campus_france" value="1" required <?php echo isset($_POST['campus_france']) ? 'checked' : ''; ?>>
            Je m'engage à suivre la procédure Campus France
          </label>
        </div>
      </div>

      <!-- Bouton -->
      <div class="form-section" style="text-align: center;">
        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Soumettre la demande</button>
      </div>
    </form>
  </div>
  
  <footer>
    <p>© 2025 Parcoursup. Tous droits réservés.</p>
  </footer>
</div>

<script>
// Fonction pour gérer la section garant
function toggleGarantSection() {
    const hasGarant = document.getElementById("a_garant_france").value;
    const garantSection = document.getElementById("garant_section");
    
    if (hasGarant === "oui") {
        garantSection.style.display = "block";
        // Rendre les champs obligatoires
        const garantFields = garantSection.querySelectorAll("input, textarea");
        garantFields.forEach(field => {
            field.required = true;
        });
    } else {
        garantSection.style.display = "none";
        // Rendre les champs non obligatoires
        const garantFields = garantSection.querySelectorAll("input, textarea");
        garantFields.forEach(field => {
            field.required = false;
        });
    }
}

// Fonction pour gérer l'affichage du certificat de scolarité
function toggleCertificatScolarite() {
    const niveau = document.getElementById("niveau_etudes").value;
    const certificatSection = document.getElementById("certificat_scolarite_section");
    const certificatInput = document.getElementById("certificat_scolarite");
    
    if (niveau === "master_termine") {
        certificatSection.style.display = "none";
        certificatInput.required = false;
    } else {
        certificatSection.style.display = "block";
        certificatInput.required = true;
    }
}

// Fonctions existantes
function toggleTestFrancais() {
  const hasTest = document.getElementById("tests_francais").value;
  const scoreTestSection = document.getElementById("score_test_section");
  const rdvButton = document.getElementById("test_rdv_button");
  
  scoreTestSection.style.display = (hasTest !== "non") ? "block" : "none";
  rdvButton.style.display = (hasTest === "non") ? "block" : "none";
}

function toggleTestAnglais() {
  const hasTest = document.getElementById("test_anglais").value;
  document.getElementById("score_anglais_section").style.display = (hasTest !== "non") ? "block" : "none";
}

function toggleBoitePastel() {
  const hasBoite = document.getElementById("boite_pastel").value;
  document.getElementById("boite_pastel_section").style.display = (hasBoite === "oui") ? "block" : "none";
}

function toggleRelevesAnnees() {
  const niveau = document.getElementById("niveau_etudes").value;
  const relevesSection = document.getElementById("releves_annees_section");
  
  // Afficher les relevés pour tous les niveaux sauf "Master terminé"
  if (niveau !== "master_termine" && 
      (niveau === "licence1" || niveau === "licence2" || niveau === "licence3" || 
       niveau === "master1" || niveau === "master2" || niveau === "doctorat" ||
       niveau === "bts" || niveau === "dut" || niveau === "inge" || niveau === "commerce")) {
    relevesSection.style.display = "block";
    genererChampsReleves(niveau);
  } else {
    relevesSection.style.display = "none";
  }
}

function genererChampsReleves(niveau) {
  const container = document.getElementById("releves_annees_container");
  container.innerHTML = "";
  
  let annees = [];
  
  switch(niveau) {
    case "licence1":
      annees = ["Année du Bac"];
      break;
    case "licence2":
      annees = ["Année du Bac", "Licence 1"];
      break;
    case "licence3":
      annees = ["Année du Bac", "Licence 1", "Licence 2"];
      break;
    case "master1":
      annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3"];
      break;
    case "master2":
      annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3", "Master 1"];
      break;
    case "doctorat":
      annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3", "Master 1", "Master 2"];
      break;
    case "bts":
      annees = ["Année du Bac", "BTS 1ère année"];
      break;
    case "dut":
      annees = ["Année du Bac", "DUT 1ère année"];
      break;
    case "inge":
      annees = ["Année du Bac", "1ère année ingénieur", "2ème année ingénieur"];
      break;
    case "commerce":
      annees = ["Année du Bac", "1ère année commerce", "2ème année commerce"];
      break;
    default:
      annees = ["Année du Bac"];
  }
  
  annees.forEach((annee, index) => {
    const anneeNum = index + 1;
    const section = document.createElement("div");
    section.className = "annee-section";
    section.innerHTML = `
      <div class="annee-header">
        <h4>${annee}</h4>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="required">Année d'étude</label>
          <input type="text" name="annee_etude_${anneeNum}" value="${annee}" readonly>
        </div>
        <div class="form-group">
          <label class="required">Moyenne</label>
          <input type="text" name="moyenne_annee_${anneeNum}" placeholder="Ex: 14.5/20" required>
        </div>
        <div class="form-group">
          <label class="required">Mention</label>
          <input type="text" name="mention_annee_${anneeNum}" placeholder="Ex: Assez Bien" required>
        </div>
      </div>
      <div class="form-group">
        <label class="required">Relevé de notes ${annee}</label>
        <input type="file" name="releve_annee_${anneeNum}" class="file-input" accept=".pdf,.jpg,.png" required>
        <span class="file-hint">Relevé de notes de ${annee} (max 5MB)</span>
      </div>
    `;
    container.appendChild(section);
  });
}

function genererChampsDocuments() {
  const nbDocuments = document.getElementById("nb_documents").value;
  const container = document.getElementById("autres_documents_container");
  container.innerHTML = "";
  
  for (let i = 1; i <= nbDocuments; i++) {
    const section = document.createElement("div");
    section.className = "document-section";
    section.innerHTML = `
      <div class="form-row">
        <div class="form-group">
          <label class="required">Type de document ${i}</label>
          <select name="type_document_${i}" required>
            <option value="">-- Sélectionnez --</option>
            <option value="lettre_recommandation">Lettre de recommandation</option>
            <option value="attestation_travail">Attestation de travail</option>
            <option value="attestation_stage">Attestation de stage</option>
            <option value="certificat_competence">Certificat de compétence</option>
            <option value="autre">Autre document</option>
          </select>
        </div>
        <div class="form-group">
          <label>Description</label>
          <input type="text" name="description_document_${i}" placeholder="Description du document">
        </div>
      </div>
      <div class="form-group">
        <label class="required">Fichier document ${i}</label>
        <input type="file" name="fichier_document_${i}" class="file-input" accept=".pdf,.jpg,.png,.doc,.docx" required>
        <span class="file-hint">Document ${i} (max 5MB)</span>
      </div>
    `;
    container.appendChild(section);
  }
}

function validateForm() {
  const niveau = document.getElementById("niveau_etudes").value;
  if (!niveau) {
    alert("Veuillez sélectionner votre niveau d'études");
    return false;
  }
  
  // Validation du certificat de scolarité (obligatoire sauf pour master terminé)
  const certificatScolarite = document.getElementById("certificat_scolarite");
  if (niveau !== "master_termine" && !certificatScolarite.files.length) {
    alert("Le certificat de scolarité est obligatoire pour tous les niveaux sauf Master terminé.");
    return false;
  }
  
  return true;
}

// Fonction pour initialiser l'état des sections conditionnelles
function initConditionalSections() {
  toggleGarantSection();
  toggleTestFrancais();
  toggleTestAnglais();
  toggleBoitePastel();
  toggleRelevesAnnees();
  toggleCertificatScolarite();
  genererChampsDocuments();
}

// Appeler les fonctions au chargement de la page
document.addEventListener('DOMContentLoaded', initConditionalSections);

// Fermer automatiquement les messages d'alerte après 5 secondes
setTimeout(() => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    alert.style.display = 'none';
  });
}, 5000);
</script>
</body>
</html>