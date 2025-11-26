<?php
session_start();

class SecureFileUpload {
    private $maxSize;
    private $allowedExtensions;
    private $allowedMimeTypes;
    private $uploadDir;
    
    public function __construct() {
        $this->maxSize = 5 * 1024 * 1024;
        $this->allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $this->allowedMimeTypes = [
            'application/pdf',
            'image/jpeg', 
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $this->uploadDir = 'uploads/' . session_id() . '/';
        $this->createUploadDir();
    }
    
    private function createUploadDir() {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function handleUpload($files) {
        $results = [];
        
        foreach ($files as $docCode => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $result = $this->validateAndSave($file, $docCode);
                $results[$docCode] = $result;
            } else {
                $results[$docCode] = [
                    'success' => false,
                    'error' => $this->getUploadError($file['error'])
                ];
            }
        }
        
        return $results;
    }
    
    private function validateAndSave($file, $docCode) {
        // Validation de l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'error' => 'Extension non autorisée'];
        }
        
        // Validation du type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return ['success' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        // Validation de la taille
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'Fichier trop volumineux'];
        }
        
        // Sanitisation du nom de fichier
        $safeName = $this->generateSafeFilename($file['name'], $docCode, $extension);
        $destination = $this->uploadDir . $safeName;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Enregistrement en session
            $_SESSION['uploaded_documents'][$docCode] = [
                'filename' => $safeName,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'uploaded_at' => time()
            ];
            
            return ['success' => true, 'filename' => $safeName];
        } else {
            return ['success' => false, 'error' => 'Erreur lors du téléchargement'];
        }
    }
    
    private function generateSafeFilename($originalName, $docCode, $extension) {
        $baseName = uniqid($docCode . '_', true);
        return $baseName . '.' . $extension;
    }
    
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
            UPLOAD_ERR_PARTIAL => 'Téléchargement interrompu',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier téléchargé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
            UPLOAD_ERR_EXTENSION => 'Extension non autorisée'
        ];
        
        return $errors[$errorCode] ?? 'Erreur inconnue';
    }
}

// Utilisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploader = new SecureFileUpload();
    $results = $uploader->handleUpload($_FILES['documents']);
    
    // Redirection avec statut
    if (count(array_filter($results, fn($r) => !$r['success'])) === 0) {
        $_SESSION['upload_success'] = true;
        header('Location: confirmation.php');
    } else {
        $_SESSION['upload_errors'] = $results;
        header('Location: upload.php?error=1');
    }
    exit;
}
?>