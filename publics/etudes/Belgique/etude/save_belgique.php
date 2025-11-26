<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données: " . $e->getMessage();
    header('Location: etude_belgique.php');
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données du formulaire
    $nom = $_POST['nom'] ?? '';
    $naissance = $_POST['naissance'] ?? '';
    $nationalite = $_POST['nationalite'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $niveau_etude = $_POST['niveau_etude'] ?? '';
    $equivalence_bac = $_POST['equivalence_bac'] ?? 'non';
    
    // Validation des champs obligatoires
    if (empty($nom) || empty($naissance) || empty($nationalite) || empty($telephone) || empty($email) || empty($adresse) || empty($niveau_etude)) {
        $_SESSION['error_message'] = "Tous les champs obligatoires doivent être remplis.";
        header('Location: etude_belgique.php');
        exit();
    }
    
    // Gestion de l'upload des fichiers
    $upload_dir = __DIR__ . '/uploads/etude_belgique/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Fonction pour uploader un fichier
    function uploadFile($file, $upload_dir, $field_name) {
        if (isset($file[$field_name]) && $file[$field_name]['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($file[$field_name]['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file[$field_name]['tmp_name'], $file_path)) {
                return $file_name;
            }
        }
        return null;
    }
    
    // Upload des fichiers obligatoires
    $photo = uploadFile($_FILES, $upload_dir, 'photo');
    $passport = uploadFile($_FILES, $upload_dir, 'passport');
    
    // Vérifier que les fichiers obligatoires sont uploadés
    if (!$photo || !$passport) {
        $_SESSION['error_message'] = "Les fichiers photo et passeport sont obligatoires.";
        header('Location: etude_belgique.php');
        exit();
    }
    
    // Upload du document d'équivalence si nécessaire
    $document_equivalence = null;
    if ($equivalence_bac === 'oui' && isset($_FILES['document_equivalence'])) {
        $document_equivalence = uploadFile($_FILES, $upload_dir, 'document_equivalence');
    }
    
    // Upload des documents académiques selon le niveau
    $releve_2nde = uploadFile($_FILES, $upload_dir, 'releve_2nde');
    $releve_1ere = uploadFile($_FILES, $upload_dir, 'releve_1ere');
    $releve_terminale = uploadFile($_FILES, $upload_dir, 'releve_terminale');
    $releve_bac = uploadFile($_FILES, $upload_dir, 'releve_bac');
    $diplome_bac = uploadFile($_FILES, $upload_dir, 'diplome_bac');
    $releve_l1 = uploadFile($_FILES, $upload_dir, 'releve_l1');
    $releve_l2 = uploadFile($_FILES, $upload_dir, 'releve_l2');
    $releve_l3 = uploadFile($_FILES, $upload_dir, 'releve_l3');
    $diplome_licence = uploadFile($_FILES, $upload_dir, 'diplome_licence');
    $certificat_scolarite = uploadFile($_FILES, $upload_dir, 'certificat_scolarite');
    
    try {
        // Insertion dans la base de données
        $sql = "INSERT INTO etude_belgique (
            nom, naissance, nationalite, telephone, email, adresse, 
            niveau_etude, equivalence_bac, photo, passport, document_equivalence,
            releve_2nde, releve_1ere, releve_terminale, releve_bac, diplome_bac,
            releve_l1, releve_l2, releve_l3, diplome_licence, certificat_scolarite,
            status, created_at
        ) VALUES (
            :nom, :naissance, :nationalite, :telephone, :email, :adresse,
            :niveau_etude, :equivalence_bac, :photo, :passport, :document_equivalence,
            :releve_2nde, :releve_1ere, :releve_terminale, :releve_bac, :diplome_bac,
            :releve_l1, :releve_l2, :releve_l3, :diplome_licence, :certificat_scolarite,
            'pending', NOW()
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':naissance' => $naissance,
            ':nationalite' => $nationalite,
            ':telephone' => $telephone,
            ':email' => $email,
            ':adresse' => $adresse,
            ':niveau_etude' => $niveau_etude,
            ':equivalence_bac' => $equivalence_bac,
            ':photo' => $photo,
            ':passport' => $passport,
            ':document_equivalence' => $document_equivalence,
            ':releve_2nde' => $releve_2nde,
            ':releve_1ere' => $releve_1ere,
            ':releve_terminale' => $releve_terminale,
            ':releve_bac' => $releve_bac,
            ':diplome_bac' => $diplome_bac,
            ':releve_l1' => $releve_l1,
            ':releve_l2' => $releve_l2,
            ':releve_l3' => $releve_l3,
            ':diplome_licence' => $diplome_licence,
            ':certificat_scolarite' => $certificat_scolarite
        ]);
        
        $_SESSION['success_message'] = "Votre demande d'études en Belgique a été soumise avec succès !";
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de l'enregistrement: " . $e->getMessage();
    }
    
    // Redirection vers le formulaire
    header('Location: index.php');
    exit();
    
} else {
    // Si la méthode n'est pas POST, rediriger vers le formulaire
    header('Location: index.php');
    exit();
}
?>