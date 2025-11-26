<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $pays = htmlspecialchars($_POST['pays']);
    $type_visa = htmlspecialchars($_POST['type_visa']);
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $telephone = htmlspecialchars($_POST['telephone']);
    
    // Traitement des fichiers uploadés
    $dossier_upload = "uploads/";
    if (!file_exists($dossier_upload)) {
        mkdir($dossier_upload, 0777, true);
    }
    
    $fichiers_uploades = [];
    
    // Fonction pour gérer l'upload sécurisé
    function uploadFichier($fichier, $dossier) {
        if ($fichier['error'] === UPLOAD_ERR_OK) {
            $nom_fichier = uniqid() . '_' . basename($fichier['name']);
            $chemin_complet = $dossier . $nom_fichier;
            
            // Vérification du type de fichier
            $types_autorises = ['jpg', 'jpeg', 'png', 'pdf'];
            $extension = strtolower(pathinfo($chemin_complet, PATHINFO_EXTENSION));
            
            if (in_array($extension, $types_autorises)) {
                if (move_uploaded_file($fichier['tmp_name'], $chemin_complet)) {
                    return $nom_fichier;
                }
            }
        }
        return null;
    }
    
    // Upload de chaque fichier
    if (!empty($_FILES['fichier_passeport'])) {
        $fichiers_uploades['passeport'] = uploadFichier($_FILES['fichier_passeport'], $dossier_upload);
    }
    
    // Génération du PDF de confirmation (simplifié)
    $contenu_pdf = "
        CONFIRMATION DE DEMANDE DE VISA
        ================================
        
        Pays: $pays
        Type de visa: $type_visa
        
        Informations personnelles:
        - Nom: $nom
        - Prénom: $prenom
        - Email: $email
        - Téléphone: $telephone
        
        Date de soumission: " . date('d/m/Y H:i:s') . "
        
        Votre demande a été enregistrée avec succès.
        Vous recevrez un email de confirmation sous peu.
    ";
    
    // Sauvegarde dans un fichier texte (remplace le PDF pour cet exemple)
    $nom_fichier_confirmation = "confirmation_" . $nom . "_" . $prenom . "_" . date('Y-m-d_H-i-s') . ".txt";
    file_put_contents($dossier_upload . $nom_fichier_confirmation, $contenu_pdf);
    
    // Affichage de la confirmation
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .confirmation { background: #d4edda; padding: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='confirmation'>
            <h2>✅ Demande Enregistrée avec Succès!</h2>
            <p><strong>Récapitulatif:</strong></p>
            <p>Pays: " . strtoupper($pays) . "</p>
            <p>Nom: $nom $prenom</p>
            <p>Type de visa: $type_visa</p>
            <p>Votre numéro de dossier: VS" . rand(100000, 999999) . "</p>
            <p>Une confirmation a été sauvegardée.</p>
            <a href='index.html'>← Retour au formulaire</a>
        </div>
    </body>
    </html>
    ";
    
} else {
    header("Location: index.html");
    exit();
}
?>