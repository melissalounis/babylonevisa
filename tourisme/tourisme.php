<?php
// Ne pas démarrer la session si elle est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration d'erreurs (désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔹 Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 🔹 Généner un token CSRF si non existant
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🔹 Connexion BDD
require_once __DIR__ . '../../config.php';

// 🔹 Utiliser la constante existante ou définir une valeur par défaut
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}

if (!defined('MAX_REQUESTS_PER_DAY')) {
    define('MAX_REQUESTS_PER_DAY', 5);
}

// 🔹 Vérifier si ALLOWED_MIME_TYPES est déjà défini
if (!defined('ALLOWED_MIME_TYPES')) {
    define('ALLOWED_MIME_TYPES', [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/jpg' => 'jpg',
        'application/pdf' => 'pdf'
    ]);
}

// 🔹 Classes de validation
class VisaFormValidator {
    private $errors = [];
    private $data = [];
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function validate(): bool {
        $this->validateRequired();
        $this->validateEmail();
        $this->validateDates();
        $this->validatePhone();
        $this->validatePassport();
        
        return empty($this->errors);
    }
    
    private function validateRequired(): void {
        $required_fields = [
            'pays_destination', 'visa_type', 'nom', 'date_naissance', 'lieu_naissance',
            'etat_civil', 'nationalite', 'profession', 'adresse', 'telephone',
            'email', 'num_passeport', 'pays_delivrance', 'date_delivrance', 'date_expiration'
        ];
        
        foreach ($required_fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[] = "Le champ " . str_replace('_', ' ', $field) . " est obligatoire.";
            }
        }
    }
    
    private function validateEmail(): void {
        if (!empty($this->data['email']) && !filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Adresse email invalide.";
        }
        
        if (!empty($this->data['email_hote']) && !filter_var($this->data['email_hote'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Adresse email de l'hôte invalide.";
        }
    }
    
    private function validateDates(): void {
        if (!empty($this->data['date_expiration']) && strtotime($this->data['date_expiration']) <= time()) {
            $this->errors[] = "La date d'expiration du passeport doit être dans le futur.";
        }
        
        if (!empty($this->data['date_naissance']) && strtotime($this->data['date_naissance']) >= time()) {
            $this->errors[] = "La date de naissance doit être dans le passé.";
        }
        
        if (!empty($this->data['date_delivrance']) && !empty($this->data['date_expiration'])) {
            if (strtotime($this->data['date_delivrance']) >= strtotime($this->data['date_expiration'])) {
                $this->errors[] = "La date de délivrance doit être antérieure à la date d'expiration.";
            }
        }
    }
    
    private function validatePhone(): void {
        if (!empty($this->data['telephone']) && !preg_match('/^[+]?[0-9\s\-\(\)]{10,20}$/', $this->data['telephone'])) {
            $this->errors[] = "Numéro de téléphone invalide.";
        }
    }
    
    private function validatePassport(): void {
        if (!empty($this->data['num_passeport']) && !preg_match('/^[A-Z0-9]{6,12}$/', $this->data['num_passeport'])) {
            $this->errors[] = "Numéro de passeport invalide. Format attendu : 6-12 caractères alphanumériques.";
        }
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getValidatedData(): array {
        return $this->data;
    }
}

class FileUploader {
    private $pdo;
    private $uploadDir;
    private $errors = [];
    
    public function __construct(PDO $pdo, string $uploadDir) {
        $this->pdo = $pdo;
        $this->uploadDir = $uploadDir;
        $this->ensureUploadDir();
    }
    
    private function ensureUploadDir(): void {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Ajouter un fichier .htaccess pour sécuriser le dossier
        $htaccess = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order deny,allow\nDeny from all\n<FilesMatch '\.(jpg|jpeg|png|pdf)$'>\nAllow from all\n</FilesMatch>");
        }
        
        // Ajouter un fichier index.html vide pour éviter le listing
        $indexFile = $this->uploadDir . 'index.html';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '<!-- Directory listing disabled -->');
        }
    }
    
    public function validateFile(array $file): bool {
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Erreur lors de l'upload du fichier : " . $this->getUploadError($file['error']);
            return false;
        }
        
        // Vérifier la taille
        if ($file['size'] > MAX_FILE_SIZE) {
            $this->errors[] = "Fichier trop volumineux (max " . (MAX_FILE_SIZE / 1024 / 1024) . "MB) : " . $file['name'];
            return false;
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_types = defined('ALLOWED_MIME_TYPES') ? ALLOWED_MIME_TYPES : [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/jpg' => 'jpg',
            'application/pdf' => 'pdf'
        ];
        
        if (!array_key_exists($mime_type, $allowed_types)) {
            $this->errors[] = "Type de fichier non autorisé : " . $mime_type;
            return false;
        }
        
        // Vérifier les extensions dangereuses
        $filename = $file['name'];
        if (preg_match('/\.(php|phtml|phar|exe|js|html|htm|asp|aspx)$/i', $filename)) {
            $this->errors[] = "Extension de fichier dangereuse détectée.";
            return false;
        }
        
        return true;
    }
    
    public function saveFile(array $file, string $type, int $demande_id): bool {
        try {
            if (!$this->validateFile($file)) {
                return false;
            }
            
            // Générer un nom de fichier sécurisé
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowed_types = defined('ALLOWED_MIME_TYPES') ? ALLOWED_MIME_TYPES : [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/jpg' => 'jpg',
                'application/pdf' => 'pdf'
            ];
            
            $extension = $allowed_types[$mime_type];
            $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
            $filename = uniqid() . "_" . md5($safe_name . time()) . "." . $extension;
            $filepath = $this->uploadDir . $filename;
            
            // Déplacer le fichier
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->errors[] = "Impossible de déplacer le fichier téléchargé.";
                return false;
            }
            
            // Changer les permissions du fichier
            chmod($filepath, 0644);
            
            // Enregistrer dans la base de données
            $stmt = $this->pdo->prepare("INSERT INTO demandes_court_sejour_fichiers 
                (demande_id, type_fichier, chemin_fichier, nom_original, date_upload) 
                VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$demande_id, $type, $filename, $safe_name]);
            
            return true;
            
        } catch (Exception $e) {
            $this->errors[] = "Erreur lors de l'enregistrement du fichier : " . $e->getMessage();
            return false;
        }
    }
    
    public function saveMultipleFiles(array $files, string $type, int $demande_id): int {
        $count = 0;
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file_data = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                if ($this->saveFile($file_data, $type, $demande_id)) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    private function getUploadError(int $error_code): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (taille maximale dépassée)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
            UPLOAD_ERR_EXTENSION => 'Extension PHP bloquée'
        ];
        
        return $errors[$error_code] ?? 'Erreur inconnue';
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
}

// 🔹 Fonctions utilitaires
function validateInput($data) {
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function checkRateLimit(PDO $pdo, int $user_id): bool {
    try {
        $max_requests = defined('MAX_REQUESTS_PER_DAY') ? MAX_REQUESTS_PER_DAY : 5;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_court_sejour 
                               WHERE user_id = ? AND DATE(date_demande) = CURDATE()");
        $stmt->execute([$user_id]);
        $count = $stmt->fetchColumn();
        
        return $count < $max_requests;
    } catch (Exception $e) {
        error_log("Erreur vérification rate limit: " . $e->getMessage());
        return true; // En cas d'erreur, on autorise pour ne pas bloquer l'utilisateur
    }
}

function initializeTables(PDO $pdo): void {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS demandes_court_sejour (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            visa_type VARCHAR(50),
            pays_destination VARCHAR(100),
            nom_complet VARCHAR(100),
            date_naissance DATE,
            lieu_naissance VARCHAR(100),
            etat_civil VARCHAR(50),
            nationalite VARCHAR(100),
            profession VARCHAR(100),
            adresse TEXT,
            telephone VARCHAR(20),
            email VARCHAR(100),
            passeport VARCHAR(50),
            pays_delivrance VARCHAR(100),
            date_delivrance DATE,
            date_expiration DATE,
            a_deja_visa VARCHAR(3),
            nb_visas INT DEFAULT 0,
            details_voyages TEXT,
            nom_hote VARCHAR(100),
            adresse_hote TEXT,
            telephone_hote VARCHAR(20),
            email_hote VARCHAR(100),
            lien_parente VARCHAR(50),
            statut VARCHAR(20) DEFAULT 'en_attente',
            date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_statut (statut),
            INDEX idx_date_demande (date_demande)
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS demandes_court_sejour_fichiers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            demande_id INT NOT NULL,
            type_fichier VARCHAR(100),
            chemin_fichier VARCHAR(255),
            nom_original VARCHAR(255),
            date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_demande_id (demande_id),
            INDEX idx_type_fichier (type_fichier)
        )");
        
    } catch (Exception $e) {
        error_log("Erreur initialisation tables: " . $e->getMessage());
        throw $e;
    }
}

// 🔹 Initialiser les variables
$form_data = [];
$errors = [];
$success = false;

try {
    // 🔹 Initialiser les tables
    initializeTables($pdo);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 🔹 Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $errors[] = "Token de sécurité invalide. Veuillez recharger la page.";
        }
        
        // 🔹 Vérifier le rate limiting
        if (!checkRateLimit($pdo, $_SESSION['user_id'])) {
            $max_requests = defined('MAX_REQUESTS_PER_DAY') ? MAX_REQUESTS_PER_DAY : 5;
            $errors[] = "Vous avez atteint la limite de " . $max_requests . " demandes par jour.";
        }
        
        if (empty($errors)) {
            // 🔹 Valider les données du formulaire
            $validator = new VisaFormValidator($_POST);
            
            if ($validator->validate()) {
                $validated_data = $validator->getValidatedData();
                
                // 🔹 Nettoyer et récupérer les données
                $pays_destination   = validateInput($validated_data['pays_destination'] ?? '');
                $visa_type          = validateInput($validated_data['visa_type'] ?? '');
                $nom                = validateInput($validated_data['nom'] ?? '');
                $date_naissance     = $validated_data['date_naissance'] ?? '';
                $lieu_naissance     = validateInput($validated_data['lieu_naissance'] ?? '');
                $etat_civil         = validateInput($validated_data['etat_civil'] ?? '');
                $nationalite        = validateInput($validated_data['nationalite'] ?? '');
                $profession         = validateInput($validated_data['profession'] ?? '');
                $adresse            = validateInput($validated_data['adresse'] ?? '');
                $telephone          = validateInput($validated_data['telephone'] ?? '');
                $email              = filter_var($validated_data['email'], FILTER_VALIDATE_EMAIL);
                $num_passeport      = validateInput($validated_data['num_passeport'] ?? '');
                $pays_delivrance    = validateInput($validated_data['pays_delivrance'] ?? '');
                $date_delivrance    = $validated_data['date_delivrance'] ?? '';
                $date_expiration    = $validated_data['date_expiration'] ?? '';
                $a_deja_visa        = $validated_data['voyages_precedents'] ?? 'non';
                $nb_visas           = intval($validated_data['nb_visas'] ?? 0);
                $details_voyages    = validateInput($validated_data['details_voyages'] ?? '');
                
                // 🔹 Champs conditionnels pour hébergement
                $nom_hote           = validateInput($validated_data['nom_hote'] ?? '');
                $adresse_hote       = validateInput($validated_data['adresse_hote'] ?? '');
                $telephone_hote     = validateInput($validated_data['telephone_hote'] ?? '');
                $email_hote         = !empty($validated_data['email_hote']) ? 
                                      filter_var($validated_data['email_hote'], FILTER_VALIDATE_EMAIL) : '';
                $lien_parente       = validateInput($validated_data['lien_parente'] ?? '');
                
                $user_id = $_SESSION['user_id'];
                
                // 🔹 Début de la transaction
                $pdo->beginTransaction();
                
                try {
                    // 🔹 Insertion dans `demandes_court_sejour`
                    $stmt = $pdo->prepare("INSERT INTO demandes_court_sejour 
                        (user_id, visa_type, pays_destination, nom_complet, date_naissance, lieu_naissance, etat_civil, 
                        nationalite, profession, adresse, telephone, email, passeport, pays_delivrance, date_delivrance, 
                        date_expiration, a_deja_visa, nb_visas, details_voyages, nom_hote, adresse_hote, telephone_hote, 
                        email_hote, lien_parente) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $user_id, $visa_type, $pays_destination, $nom, $date_naissance, $lieu_naissance, $etat_civil,
                        $nationalite, $profession, $adresse, $telephone, $email, $num_passeport, $pays_delivrance,
                        $date_delivrance, $date_expiration, $a_deja_visa, $nb_visas, $details_voyages,
                        $nom_hote, $adresse_hote, $telephone_hote, $email_hote, $lien_parente
                    ]);
                    
                    $demande_id = $pdo->lastInsertId();
                    
                    // 🔹 Dossier uploads - Correction du chemin
                    $baseDir = realpath(__DIR__ . '/../../');
                    if (!$baseDir) {
                        $baseDir = dirname(__DIR__, 2);
                    }
                    
                    $uploadDir = $baseDir . '/uploads/visas/court_sejour/';
                    
                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Vérifier que le chemin est sécurisé (optionnel)
                    $realUploadDir = realpath($uploadDir);
                    $realBaseDir = realpath($baseDir . '/uploads/');
                    
                    if ($realUploadDir && $realBaseDir && strpos($realUploadDir, $realBaseDir) === 0) {
                        // Chemin valide
                    } else {
                        // Log l'erreur mais continue
                        error_log("Chemin d'upload potentiellement invalide: " . $uploadDir);
                    }
                    
                    // 🔹 Initialiser l'uploader de fichiers
                    $fileUploader = new FileUploader($pdo, $uploadDir);
                    
                    // 🔹 Traitement fichiers simples
                    $file_fields = [
                        'copie_passeport' => 'copie_passeport',
                        'documents_travail' => 'documents_travail',
                        'lettre_invitation' => 'lettre_invitation',
                        'justificatif_ressources' => 'justificatif_ressources',
                        'lettre_prise_en_charge' => 'lettre_prise_en_charge',
                        'prise_en_charge_entreprise' => 'prise_en_charge_entreprise',
                        'invitation_entreprise' => 'invitation_entreprise'
                    ];
                    
                    foreach ($file_fields as $field => $type) {
                        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                            $fileUploader->saveFile($_FILES[$field], $type, $demande_id);
                        }
                    }
                    
                    // 🔹 Traitement fichiers multiples (documents de travail)
                    if (!empty($_FILES['documents_travail_multiple']['name'][0])) {
                        $fileUploader->saveMultipleFiles(
                            $_FILES['documents_travail_multiple'], 
                            'documents_travail', 
                            $demande_id
                        );
                    }
                    
                    // 🔹 Traitement fichiers multiples (visas précédents)
                    if ($a_deja_visa === 'oui' && !empty($_FILES['copies_visas']['name'][0])) {
                        $fileUploader->saveMultipleFiles(
                            $_FILES['copies_visas'], 
                            'copie_visa', 
                            $demande_id
                        );
                    }
                    
                    // 🔹 Valider la transaction
                    $pdo->commit();
                    
                    // 🔹 Régénérer le token CSRF
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    // 🔹 Redirection avec confirmation
                    $_SESSION['form_success'] = true;
                    $_SESSION['last_demande_id'] = $demande_id;
                    
                    header("Location: confirmation.php?id=" . $demande_id . "&token=" . bin2hex(random_bytes(16)));
                    exit;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                
            } else {
                $errors = array_merge($errors, $validator->getErrors());
                $form_data = $_POST;
            }
        } else {
            $form_data = $_POST;
        }
    } else {
        // Pour les requêtes GET, régénérer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

} catch (PDOException $e) {
    error_log("Erreur BDD formulaire visa: " . $e->getMessage());
    $errors[] = "Erreur de base de données. Veuillez réessayer plus tard.";
} catch (Exception $e) {
    error_log("Erreur générale formulaire visa: " . $e->getMessage());
    $errors[] = "Une erreur est survenue : " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire de Visa Court Séjour</title>
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
      --warning-color: #ffc107;
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
    
    .form-content {
      padding: 30px;
    }
    
    .form-section {
      margin-bottom: 30px;
      padding: 25px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: var(--light-gray);
      transition: var(--transition);
    }
    
    .form-section.active {
      border-left: 4px solid var(--primary-color);
      box-shadow: var(--box-shadow);
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
      color: var(--primary-color);
    }
    
    .form-group {
      margin-bottom: 20px;
      position: relative;
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
    
    input.error, select.error, textarea.error {
      border-color: var(--error-color);
    }
    
    .error-message {
      color: var(--error-color);
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
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
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
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
    
    .btn-submit:hover:not(:disabled) {
      background: linear-gradient(to right, #002244, #004488);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    .btn-submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
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
      border-left: 4px solid var(--secondary-color);
      padding-left: 15px;
      margin-top: 15px;
      margin-bottom: 15px;
      animation: fadeIn 0.3s ease;
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
      display: flex;
      align-items: center;
    }
    
    .alert i {
      margin-right: 10px;
      font-size: 1.2rem;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
    
    .alert-success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    
    .alert-warning {
      background-color: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
    }
    
    .progress-container {
      margin-bottom: 30px;
      background: var(--light-gray);
      padding: 20px;
      border-radius: var(--border-radius);
    }
    
    .progress-steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-weight: 600;
    }
    
    .progress-bar {
      height: 10px;
      background: #f0f0f0;
      border-radius: 5px;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      background: var(--primary-color);
      border-radius: 5px;
      transition: width 0.3s ease;
      width: 33%;
    }
    
    .char-counter {
      font-size: 0.8rem;
      color: #666;
      text-align: right;
      margin-top: 5px;
    }
    
    .rate-limit-info {
      background: var(--light-blue);
      padding: 10px 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
      
      .progress-steps {
        font-size: 0.8rem;
      }
      
      .form-section {
        padding: 15px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-passport"></i> Demande de Visa Court Séjour</h1>
    <p>Remplissez soigneusement tous les champs obligatoires (*)</p>
  </header>

  <!-- Informations sur la limite de demandes -->
  <div class="rate-limit-info">
    <i class="fas fa-info-circle"></i> 
    Limite : <?php echo defined('MAX_REQUESTS_PER_DAY') ? MAX_REQUESTS_PER_DAY : 5; ?> demandes par jour maximum
  </div>

  <!-- Affichage des erreurs -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-triangle"></i>
      <div>
        <strong>Erreurs :</strong>
        <ul style="margin-top: 10px; margin-left: 20px;">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <!-- Indicateur de progression -->
  <div class="progress-container">
    <div class="progress-steps">
      <span>Informations personnelles</span>
      <span>Documents</span>
      <span>Validation</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" id="progress-fill"></div>
    </div>
  </div>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" id="visa-form" novalidate>
      <!-- Token CSRF -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      
      <!-- Destination -->
      <div class="form-section active">
        <h3><i class="fas fa-globe"></i> Destination</h3>
        <div class="form-group">
          <label class="required">Pays de destination</label>
          <input type="text" name="pays_destination" required 
                 placeholder="Entrez le pays de destination" 
                 value="<?= htmlspecialchars($form_data['pays_destination'] ?? '') ?>"
                 oninput="validateField(this)">
          <div class="error-message" id="error-pays_destination"></div>
        </div>
      </div>
      
      <!-- Choix du type de visa -->
      <div class="form-section">
        <h3><i class="fas fa-tasks"></i> Type de visa</h3>
        <div class="form-group">
          <label class="required">Choisissez le type de visa</label>
          <select id="visa_type" name="visa_type" required onchange="toggleVisaType()">
            <option value="">-- Sélectionnez --</option>
            <option value="tourisme" <?= ($form_data['visa_type'] ?? '') == 'tourisme' ? 'selected' : '' ?>>Visa Tourisme</option>
            <option value="affaires" <?= ($form_data['visa_type'] ?? '') == 'affaires' ? 'selected' : '' ?>>Visa Affaires</option>
            <option value="visite_familiale" <?= ($form_data['visa_type'] ?? '') == 'visite_familiale' ? 'selected' : '' ?>>Visite Familiale</option>
            <option value="autre" <?= ($form_data['visa_type'] ?? '') == 'autre' ? 'selected' : '' ?>>Autre</option>
          </select>
          <div class="error-message" id="error-visa_type"></div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user"></i> Informations personnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom complet</label>
            <input type="text" name="nom" required 
                   value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-nom"></div>
          </div>
          <div class="form-group">
            <label class="required">Date de naissance</label>
            <input type="date" name="date_naissance" required 
                   max="<?= date('Y-m-d', strtotime('-1 day')) ?>"
                   value="<?= htmlspecialchars($form_data['date_naissance'] ?? '') ?>"
                   onchange="validateField(this)">
            <div class="error-message" id="error-date_naissance"></div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" required 
                   value="<?= htmlspecialchars($form_data['lieu_naissance'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-lieu_naissance"></div>
          </div>
          <div class="form-group">
            <label class="required">État civil</label>
            <select name="etat_civil" required onchange="validateField(this)">
              <option value="">-- Sélectionnez --</option>
              <option value="celibataire" <?= ($form_data['etat_civil'] ?? '') == 'celibataire' ? 'selected' : '' ?>>Célibataire</option>
              <option value="marie" <?= ($form_data['etat_civil'] ?? '') == 'marie' ? 'selected' : '' ?>>Marié(e)</option>
              <option value="divorce" <?= ($form_data['etat_civil'] ?? '') == 'divorce' ? 'selected' : '' ?>>Divorcé(e)</option>
              <option value="veuf" <?= ($form_data['etat_civil'] ?? '') == 'veuf' ? 'selected' : '' ?>>Veuf/Veuve</option>
            </select>
            <div class="error-message" id="error-etat_civil"></div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nationalité</label>
            <input type="text" name="nationalite" required 
                   value="<?= htmlspecialchars($form_data['nationalite'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-nationalite"></div>
          </div>
          <div class="form-group">
            <label class="required">Profession</label>
            <input type="text" name="profession" required 
                   value="<?= htmlspecialchars($form_data['profession'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-profession"></div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Adresse</label>
            <input type="text" name="adresse" required 
                   value="<?= htmlspecialchars($form_data['adresse'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-adresse"></div>
          </div>
          <div class="form-group">
            <label class="required">Téléphone</label>
            <input type="tel" name="telephone" required 
                   pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                   placeholder="+33 1 23 45 67 89"
                   value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-telephone"></div>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Email</label>
          <input type="email" name="email" required 
                 value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                 oninput="validateField(this)">
          <div class="error-message" id="error-email"></div>
        </div>

        <div class="form-group">
          <label>Documents de travail (plusieurs fichiers possibles)</label>
          <input type="file" name="documents_travail_multiple[]" class="file-input" 
                 accept=".pdf,.jpg,.png" multiple
                 onchange="validateFileSize(this)">
          <span class="file-hint">Contrat de travail, fiche de paie, etc. (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB par fichier)</span>
          <div class="error-message" id="error-documents_travail_multiple"></div>
        </div>
      </div>

      <!-- Passeport -->
      <div class="form-section">
        <h3><i class="fas fa-id-card"></i> Passeport</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Numéro de passeport</label>
            <input type="text" name="num_passeport" required 
                   pattern="[A-Z0-9]{6,12}"
                   placeholder="Ex: AB123456"
                   value="<?= htmlspecialchars($form_data['num_passeport'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-num_passeport"></div>
          </div>
          <div class="form-group">
            <label class="required">Pays de délivrance</label>
            <input type="text" name="pays_delivrance" required 
                   value="<?= htmlspecialchars($form_data['pays_delivrance'] ?? '') ?>"
                   oninput="validateField(this)">
            <div class="error-message" id="error-pays_delivrance"></div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de délivrance</label>
            <input type="date" name="date_delivrance" required 
                   max="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['date_delivrance'] ?? '') ?>"
                   onchange="validateDates(this)">
            <div class="error-message" id="error-date_delivrance"></div>
          </div>
          <div class="form-group">
            <label class="required">Date d'expiration</label>
            <input type="date" name="date_expiration" required 
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   value="<?= htmlspecialchars($form_data['date_expiration'] ?? '') ?>"
                   onchange="validateDates(this)">
            <div class="error-message" id="error-date_expiration"></div>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Copie du passeport (PDF ou image)</label>
          <input type="file" name="copie_passeport" class="file-input" 
                 accept=".pdf,.jpg,.png" required
                 onchange="validateFileSize(this)">
          <span class="file-hint">Pages avec photo et informations personnelles (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
          <div class="error-message" id="error-copie_passeport"></div>
        </div>
      </div>

      <!-- Section Tourisme -->
      <div id="tourisme_section" class="form-section hidden">
        <h3><i class="fas fa-suitcase-rolling"></i> Voyage (Tourisme)</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Date d'arrivée prévue</label>
            <input type="date" name="date_arrivee" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['date_arrivee'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Date de départ prévue</label>
            <input type="date" name="date_depart" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['date_depart'] ?? '') ?>">
          </div>
        </div>

        <h3><i class="fas fa-hotel"></i> Hébergement</h3>
        <div class="form-group">
          <label>Type d'hébergement</label>
          <select id="hebergement_type" name="hebergement_type" onchange="toggleHebergementFields()">
            <option value="">-- Sélectionnez --</option>
            <option value="hotel" <?= ($form_data['hebergement_type'] ?? '') == 'hotel' ? 'selected' : '' ?>>Hôtel</option>
            <option value="particulier" <?= ($form_data['hebergement_type'] ?? '') == 'particulier' ? 'selected' : '' ?>>Chez un particulier</option>
          </select>
        </div>
        
        <!-- Champs conditionnels pour l'hébergement -->
        <div id="hotel_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom de l'hôtel</label>
            <input type="text" name="nom_hotel" placeholder="Nom de l'établissement" 
                   value="<?= htmlspecialchars($form_data['nom_hotel'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Adresse de l'hôtel</label>
            <input type="text" name="adresse_hotel" placeholder="Adresse complète de l'hôtel" 
                   value="<?= htmlspecialchars($form_data['adresse_hotel'] ?? '') ?>">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone de l'hôtel</label>
              <input type="tel" name="telephone_hotel" 
                     pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                     value="<?= htmlspecialchars($form_data['telephone_hotel'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email de l'hôtel</label>
              <input type="email" name="email_hotel" 
                     value="<?= htmlspecialchars($form_data['email_hotel'] ?? '') ?>">
            </div>
          </div>
        </div>
        
        <div id="particulier_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom et prénom de l'hôte</label>
            <input type="text" name="nom_hote" 
                   value="<?= htmlspecialchars($form_data['nom_hote'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Adresse complète de l'hôte</label>
            <input type="text" name="adresse_hote" 
                   value="<?= htmlspecialchars($form_data['adresse_hote'] ?? '') ?>">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone de l'hôte</label>
              <input type="tel" name="telephone_hote" 
                     pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                     value="<?= htmlspecialchars($form_data['telephone_hote'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email de l'hôte</label>
              <input type="email" name="email_hote" 
                     value="<?= htmlspecialchars($form_data['email_hote'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Lien de parenté</label>
            <input type="text" name="lien_parente" placeholder="Ex: Frère, Cousin, Ami, etc." 
                   value="<?= htmlspecialchars($form_data['lien_parente'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Lettre d'invitation</label>
            <input type="file" name="lettre_invitation" class="file-input" 
                   accept=".pdf,.jpg,.png"
                   onchange="validateFileSize(this)">
            <span class="file-hint">Lettre d'invitation de votre hôte (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
          </div>
        </div>
        
        <div class="form-group">
          <label>Itinéraire de voyage</label>
          <textarea name="itineraire" rows="3" maxlength="1000"
                    placeholder="Décrivez votre itinéraire et les villes que vous prévoyez de visiter..."
                    oninput="updateCharCounter(this)"><?= htmlspecialchars($form_data['itineraire'] ?? '') ?></textarea>
          <div class="char-counter" id="itineraire-counter">0/1000</div>
        </div>
      </div>

      <!-- Section Affaires -->
      <div id="affaires_section" class="form-section hidden">
        <h3><i class="fas fa-briefcase"></i> Informations professionnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Nom de l'entreprise</label>
            <input type="text" name="entreprise_origine" 
                   value="<?= htmlspecialchars($form_data['entreprise_origine'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Votre poste</label>
            <input type="text" name="poste" 
                   value="<?= htmlspecialchars($form_data['poste'] ?? '') ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label>Adresse de l'entreprise</label>
          <input type="text" name="adresse_entreprise" 
                 value="<?= htmlspecialchars($form_data['adresse_entreprise'] ?? '') ?>">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Téléphone de l'entreprise</label>
            <input type="tel" name="tel_entreprise" 
                   pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                   value="<?= htmlspecialchars($form_data['tel_entreprise'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Email de l'entreprise</label>
            <input type="email" name="email_entreprise" 
                   value="<?= htmlspecialchars($form_data['email_entreprise'] ?? '') ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label>Nom de l'entreprise de destination</label>
          <input type="text" name="entreprise_destination" 
                 value="<?= htmlspecialchars($form_data['entreprise_destination'] ?? '') ?>">
        </div>
        
        <div class="form-group">
          <label>Adresse de l'entreprise de destination</label>
          <input type="text" name="adresse_entreprise_destination" 
                 value="<?= htmlspecialchars($form_data['adresse_entreprise_destination'] ?? '') ?>">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Personne à contacter</label>
            <input type="text" name="contact_destination" 
                   value="<?= htmlspecialchars($form_data['contact_destination'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Téléphone du contact</label>
            <input type="tel" name="tel_contact_destination" 
                   pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                   value="<?= htmlspecialchars($form_data['tel_contact_destination'] ?? '') ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label>Objet de la mission</label>
          <textarea name="objet_mission" rows="3" maxlength="500"
                    oninput="updateCharCounter(this)"><?= htmlspecialchars($form_data['objet_mission'] ?? '') ?></textarea>
          <div class="char-counter" id="objet_mission-counter">0/500</div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Date de début de mission</label>
            <input type="date" name="debut_mission" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['debut_mission'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Date de fin de mission</label>
            <input type="date" name="fin_mission" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['fin_mission'] ?? '') ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label>Lettre d'invitation de l'entreprise</label>
          <input type="file" name="invitation_entreprise" class="file-input" 
                 accept=".pdf,.jpg,.png"
                 onchange="validateFileSize(this)">
          <span class="file-hint">Document officiel sur papier entête de l'entreprise (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
        </div>
      </div>

      <!-- Section Visite Familiale -->
      <div id="visite_familiale_section" class="form-section hidden">
        <h3><i class="fas fa-users"></i> Visite Familiale</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Date d'arrivée prévue</label>
            <input type="date" name="date_arrivee_familiale" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['date_arrivee_familiale'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Date de départ prévue</label>
            <input type="date" name="date_depart_familiale" 
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($form_data['date_depart_familiale'] ?? '') ?>">
          </div>
        </div>

        <h3><i class="fas fa-home"></i> Hébergement chez un particulier</h3>
        <div class="form-group">
          <label>Nom et prénom de l'hôte</label>
          <input type="text" name="nom_hote" 
                 value="<?= htmlspecialchars($form_data['nom_hote'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Adresse complète de l'hôte</label>
          <input type="text" name="adresse_hote" 
                 value="<?= htmlspecialchars($form_data['adresse_hote'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Téléphone de l'hôte</label>
            <input type="tel" name="telephone_hote" 
                   pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                   value="<?= htmlspecialchars($form_data['telephone_hote'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Email de l'hôte</label>
            <input type="email" name="email_hote" 
                   value="<?= htmlspecialchars($form_data['email_hote'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Lien de parenté</label>
          <input type="text" name="lien_parente" placeholder="Ex: Frère, Cousin, Ami, etc." 
                 value="<?= htmlspecialchars($form_data['lien_parente'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Lettre d'invitation</label>
          <input type="file" name="lettre_invitation" class="file-input" 
                 accept=".pdf,.jpg,.png"
                 onchange="validateFileSize(this)">
          <span class="file-hint">Lettre d'invitation de votre hôte (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
        </div>
      </div>

      <!-- Ressources financières -->
      <div class="form-section">
        <h3><i class="fas fa-euro-sign"></i> Ressources financières</h3>
        <div class="form-group">
          <label>Moyens de subsistance</label>
          <select id="ressources" name="ressources" onchange="toggleRessourcesFields()">
            <option value="">-- Sélectionnez --</option>
            <option value="moi_meme" <?= ($form_data['ressources'] ?? '') == 'moi_meme' ? 'selected' : '' ?>>Moi-même</option>
            <option value="garant" <?= ($form_data['ressources'] ?? '') == 'garant' ? 'selected' : '' ?>>Prise en charge par garant</option>
            <option value="entreprise" <?= ($form_data['ressources'] ?? '') == 'entreprise' ? 'selected' : '' ?>>Prise en charge par l'entreprise</option>
          </select>
        </div>
        
        <!-- Champs conditionnels pour les ressources -->
        <div id="moi_meme_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Justificatif de ressources personnelles</label>
            <input type="file" name="justificatif_ressources" class="file-input" 
                   accept=".pdf,.jpg,.png"
                   onchange="validateFileSize(this)">
            <span class="file-hint">Relevés bancaires, fiches de paie, etc. (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
          </div>
        </div>
        
        <div id="garant_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom et prénom du garant</label>
            <input type="text" name="nom_garant" 
                   value="<?= htmlspecialchars($form_data['nom_garant'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Adresse du garant</label>
            <input type="text" name="adresse_garant" 
                   value="<?= htmlspecialchars($form_data['adresse_garant'] ?? '') ?>">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone du garant</label>
              <input type="tel" name="telephone_garant" 
                     pattern="^[+]?[0-9\s\-\(\)]{10,20}$"
                     value="<?= htmlspecialchars($form_data['telephone_garant'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email du garant</label>
              <input type="email" name="email_garant" 
                     value="<?= htmlspecialchars($form_data['email_garant'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Lettre de prise en charge</label>
            <input type="file" name="lettre_prise_en_charge" class="file-input" 
                   accept=".pdf,.jpg,.png"
                   onchange="validateFileSize(this)">
            <span class="file-hint">Lettre de prise en charge signée (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
          </div>
        </div>
        
        <div id="entreprise_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Lettre de prise en charge de l'entreprise</label>
            <input type="file" name="prise_en_charge_entreprise" class="file-input" 
                   accept=".pdf,.jpg,.png"
                   onchange="validateFileSize(this)">
            <span class="file-hint">Lettre de prise en charge de l'entreprise (PDF, JPG, PNG - max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB)</span>
          </div>
        </div>
      </div>

      <!-- Voyages précédents -->
      <div class="form-section">
        <h3><i class="fas fa-plane-departure"></i> Voyages précédents</h3>
        <div class="form-group">
          <label>Avez-vous déjà voyagé dans le pays de destination ou dans l'espace Schengen ?</label>
          <select name="voyages_precedents" onchange="toggleVisaFields()">
            <option value="non" <?= ($form_data['voyages_precedents'] ?? 'non') == 'non' ? 'selected' : '' ?>>Non</option>
            <option value="oui" <?= ($form_data['voyages_precedents'] ?? '') == 'oui' ? 'selected' : '' ?>>Oui</option>
          </select>
          <!-- Champ caché pour faciliter le traitement -->
          <input type="hidden" name="a_deja_visa" id="a_deja_visa" value="<?= $form_data['a_deja_visa'] ?? 'non' ?>">
        </div>
        
        <div id="details_visa" style="display:none;">
          <div class="form-group">
            <label>Nombre de visas obtenus</label>
            <input type="number" name="nb_visas" id="nb_visas" min="0" max="10" 
                   value="<?= $form_data['nb_visas'] ?? 0 ?>" onchange="generateVisaInputs()">
          </div>
          <div id="visa_inputs"></div>
          
          <div class="form-group">
            <label>Détails des voyages précédents</label>
            <textarea name="details_voyages" rows="3" maxlength="1000"
                      placeholder="Décrivez vos voyages précédents, dates et destinations..."
                      oninput="updateCharCounter(this)"><?= htmlspecialchars($form_data['details_voyages'] ?? '') ?></textarea>
            <div class="char-counter" id="details_voyages-counter">0/1000</div>
          </div>
        </div>
      </div>

      <!-- Déclaration -->
      <div class="form-section">
        <h3><i class="fas fa-file-signature"></i> Déclaration</h3>
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="declaration" required <?= isset($form_data['declaration']) ? 'checked' : '' ?>>
            Je certifie que les informations fournies sont exactes et complètes
          </label>
          <div class="error-message" id="error-declaration"></div>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="conditions" required <?= isset($form_data['conditions']) ? 'checked' : '' ?>>
            J'accepte les conditions de traitement de mes données personnelles
          </label>
          <div class="error-message" id="error-conditions"></div>
        </div>
      </div>

      <!-- Bouton -->
      <div class="form-section" style="text-align: center;">
        <button type="submit" class="btn-submit" id="submit-btn">
          <i class="fas fa-paper-plane"></i> Soumettre la demande
        </button>
      </div>
    </form>
  </div>
  
  <footer>
    <p>© <?= date('Y') ?> Babylone Service. Tous droits réservés.</p>
  </footer>
</div>

<script>
// Configuration
const MAX_FILE_SIZE = <?= MAX_FILE_SIZE ?>; // Récupérer depuis PHP

// Fonction pour valider le formulaire
function validateForm() {
    let isValid = true;
    
    // Valider tous les champs requis
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim() && field.type !== 'checkbox') {
            showError(field, 'Ce champ est obligatoire');
            isValid = false;
        }
        
        if (field.type === 'checkbox' && !field.checked) {
            showError(field, 'Vous devez accepter cette condition');
            isValid = false;
        }
    });
    
    // Validation email
    const email = document.querySelector("input[name='email']");
    if (email && email.value && !isValidEmail(email.value)) {
        showError(email, 'Veuillez entrer une adresse email valide');
        isValid = false;
    }
    
    // Validation email hôte si présent
    const emailHote = document.querySelector("input[name='email_hote']");
    if (emailHote && emailHote.value && !isValidEmail(emailHote.value)) {
        showError(emailHote, 'Veuillez entrer une adresse email valide pour l\'hôte');
        isValid = false;
    }
    
    // Validation des dates
    const dateExpiration = document.querySelector("input[name='date_expiration']");
    if (dateExpiration && dateExpiration.value) {
        const expirationDate = new Date(dateExpiration.value);
        if (expirationDate <= new Date()) {
            showError(dateExpiration, 'La date d\'expiration doit être dans le futur');
            isValid = false;
        }
    }
    
    // Validation des fichiers
    const fileInputs = document.querySelectorAll('input[type="file"]');
    for (let input of fileInputs) {
        if (input.files.length > 0) {
            for (let file of input.files) {
                if (file.size > MAX_FILE_SIZE) {
                    alert(`Le fichier ${file.name} est trop volumineux (max ${MAX_FILE_SIZE / 1024 / 1024}MB)`);
                    isValid = false;
                    break;
                }
                
                // Vérifier l'extension
                const allowedExtensions = /(\.pdf|\.jpg|\.jpeg|\.png)$/i;
                if (!allowedExtensions.exec(file.name)) {
                    alert(`Le fichier ${file.name} n'a pas une extension autorisée (PDF, JPG, PNG)`);
                    isValid = false;
                    break;
                }
            }
        }
    }
    
    // Si valide, désactiver le bouton pour éviter les double-clics
    if (isValid) {
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    }
    
    return isValid;
}

// Validation en temps réel
function validateField(field) {
    const errorElement = document.getElementById(`error-${field.name}`);
    
    // Réinitialiser l'erreur
    field.classList.remove('error');
    if (errorElement) errorElement.style.display = 'none';
    
    // Validation spécifique par type
    if (field.hasAttribute('required') && !field.value.trim()) {
        showError(field, 'Ce champ est obligatoire');
        return false;
    }
    
    if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
        showError(field, 'Email invalide');
        return false;
    }
    
    if (field.type === 'tel' && field.value && !isValidPhone(field.value)) {
        showError(field, 'Téléphone invalide');
        return false;
    }
    
    if (field.name === 'num_passeport' && field.value && !/^[A-Z0-9]{6,12}$/.test(field.value)) {
        showError(field, 'Format invalide (6-12 caractères alphanumériques majuscules)');
        return false;
    }
    
    return true;
}

function validateDates(dateField) {
    const dateDelivrance = document.querySelector("input[name='date_delivrance']");
    const dateExpiration = document.querySelector("input[name='date_expiration']");
    
    if (dateDelivrance.value && dateExpiration.value) {
        const delivrance = new Date(dateDelivrance.value);
        const expiration = new Date(dateExpiration.value);
        
        if (delivrance >= expiration) {
            showError(dateExpiration, 'La date d\'expiration doit être postérieure à la date de délivrance');
            return false;
        }
    }
    
    return true;
}

function validateFileSize(fileInput) {
    for (let file of fileInput.files) {
        if (file.size > MAX_FILE_SIZE) {
            alert(`Le fichier ${file.name} est trop volumineux (max ${MAX_FILE_SIZE / 1024 / 1024}MB)`);
            fileInput.value = '';
            return false;
        }
    }
    return true;
}

function showError(field, message) {
    field.classList.add('error');
    const errorElement = document.getElementById(`error-${field.name}`);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[+]?[0-9\s\-\(\)]{10,20}$/;
    return re.test(phone);
}

// Fonction pour initialiser l'état des sections conditionnelles
function initConditionalSections() {
  toggleVisaType();
  toggleHebergementFields();
  toggleRessourcesFields();
  toggleVisaFields();
  
  // Si on a déjà des visas, générer les champs
  const nbVisas = parseInt(document.getElementById("nb_visas").value) || 0;
  if (nbVisas > 0) {
    generateVisaInputs();
  }
  
  // Initialiser les compteurs de caractères
  document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
    updateCharCounter(textarea);
  });
  
  // Activer la validation en temps réel
  document.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('blur', function() {
      validateField(this);
    });
  });
}

function toggleVisaType() {
  const type = document.getElementById("visa_type").value;
  document.getElementById("tourisme_section").style.display = type === "tourisme" ? "block" : "none";
  document.getElementById("affaires_section").style.display = type === "affaires" ? "block" : "none";
  document.getElementById("visite_familiale_section").style.display = type === "visite_familiale" ? "block" : "none";
  
  // Mise à jour de la barre de progression
  updateProgressBar();
}

function toggleHebergementFields() {
  const type = document.getElementById("hebergement_type").value;
  document.getElementById("hotel_fields").style.display = type === "hotel" ? "block" : "none";
  document.getElementById("particulier_fields").style.display = type === "particulier" ? "block" : "none";
}

function toggleRessourcesFields() {
  const type = document.getElementById("ressources").value;
  document.getElementById("moi_meme_fields").style.display = type === "moi_meme" ? "block" : "none";
  document.getElementById("garant_fields").style.display = type === "garant" ? "block" : "none";
  document.getElementById("entreprise_fields").style.display = type === "entreprise" ? "block" : "none";
}

function toggleVisaFields() {
  const select = document.querySelector("select[name='voyages_precedents']");
  const details = document.getElementById("details_visa");
  if (select) {
    const isVisible = select.value === "oui";
    details.style.display = isVisible ? "block" : "none";
    // Mettre à jour le champ hidden pour a_deja_visa
    document.getElementById("a_deja_visa").value = select.value;
  }
}

function generateVisaInputs() {
  const nb = parseInt(document.getElementById("nb_visas").value) || 0;
  const container = document.getElementById("visa_inputs");
  container.innerHTML = "";

  for (let i = 1; i <= nb; i++) {
    const div = document.createElement("div");
    div.classList.add("form-group");
    div.innerHTML = `<label>Copie du visa n°${i}</label>
                     <input type="file" name="copies_visas[]" class="file-input" 
                            accept=".pdf,.jpg,.png" onchange="validateFileSize(this)">
                     <span class="file-hint">Copie du visa précédent (PDF, JPG, PNG - max ${MAX_FILE_SIZE / 1024 / 1024}MB)</span>`;
    container.appendChild(div);
  }
}

function updateProgressBar() {
  const progressFill = document.getElementById('progress-fill');
  const visaType = document.getElementById('visa_type').value;
  
  if (visaType) {
    progressFill.style.width = '66%';
  } else {
    progressFill.style.width = '33%';
  }
}

function updateCharCounter(textarea) {
  const counterId = textarea.name + '-counter';
  const counter = document.getElementById(counterId);
  if (counter) {
    counter.textContent = textarea.value.length + '/' + textarea.maxLength;
  }
}

// Mettre à jour la progression lors de la saisie
document.addEventListener('input', function(e) {
  if (e.target.matches('input, select, textarea')) {
    updateProgressBar();
  }
});

// Empêcher la double soumission
let formSubmitted = false;
document.getElementById('visa-form').addEventListener('submit', function(e) {
  if (formSubmitted) {
    e.preventDefault();
    return false;
  }
  
  if (validateForm()) {
    formSubmitted = true;
    return true;
  } else {
    e.preventDefault();
    return false;
  }
});

// Appeler les fonctions au chargement de la page pour initialiser l'état
document.addEventListener('DOMContentLoaded', initConditionalSections);
</script>
</body>
</html>