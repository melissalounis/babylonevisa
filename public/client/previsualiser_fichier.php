<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si le nom du fichier est fourni
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Fichier non spécifié.");
}

$filename = basename($_GET['file']); // Protection contre les path traversal

// Paramètres de connexion
require_once __DIR__ . '../../../config.php';

// Vérifier que l'utilisateur a accès à ce fichier
try {
    
    // Récupérer l'email de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    
    if (!$user || !isset($user['email'])) {
        die("Utilisateur non trouvé.");
    }

    $user_email = $user['email'];

    // Vérifier que le fichier appartient à une demande de l'utilisateur et récupérer les infos
    $stmt = $pdo->prepare("
        SELECT *,
        CASE 
            WHEN certificat_file = ? THEN 'Certificat de langue'
            WHEN releve_2nde = ? THEN 'Relevé de notes 2nde'
            WHEN releve_1ere = ? THEN 'Relevé de notes 1ère'
            WHEN releve_terminale = ? THEN 'Relevé de notes Terminale'
            WHEN releve_bac = ? THEN 'Relevé de notes Bac'
            WHEN diplome_bac = ? THEN 'Diplôme Bac'
            WHEN certificat_scolarite = ? THEN 'Certificat de scolarité'
            ELSE 'Document'
        END as document_type
        FROM demandes_etudes_roumanie 
        WHERE email = ? AND (
            certificat_file = ? OR 
            releve_2nde = ? OR 
            releve_1ere = ? OR 
            releve_terminale = ? OR 
            releve_bac = ? OR 
            diplome_bac = ? OR 
            certificat_scolarite = ?
        )
        LIMIT 1
    ");
    
    $stmt->execute([
        $filename, $filename, $filename, $filename, $filename, $filename, $filename,
        $user_email,
        $filename, $filename, $filename, $filename, $filename, $filename, $filename
    ]);
    
    $demande = $stmt->fetch();
    
    if (!$demande) {
        die("Vous n'avez pas l'autorisation d'accéder à ce fichier.");
    }

} catch (PDOException $e) {
    die("Erreur de vérification des permissions: " . $e->getMessage());
}

// Chemins possibles pour les fichiers - À ADAPTER SELON VOTRE STRUCTURE
$possible_paths = [
    __DIR__ . '/../../uploads/roumanie/' . $filename,
    __DIR__ . '/../uploads/roumanie/' . $filename,
    __DIR__ . '/uploads/roumanie/' . $filename,
    '../../uploads/roumanie/' . $filename,
    '../uploads/roumanie/' . $filename,
    './uploads/roumanie/' . $filename,
    'uploads/roumanie/' . $filename
];

$file_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path) && is_file($path)) {
        $file_path = $path;
        break;
    }
}

// Si le fichier n'est trouvé dans aucun des chemins
if (!$file_path) {
    // Essayer de trouver le fichier en parcourant les dossiers
    $found = false;
    $upload_dirs = [
        __DIR__ . '/../../uploads/',
        __DIR__ . '/../uploads/',
        __DIR__ . '/uploads/',
        '../../uploads/',
        '../uploads/',
        './uploads/'
    ];
    
    foreach ($upload_dirs as $base_dir) {
        $roumanie_dir = $base_dir . 'roumanie/';
        if (is_dir($roumanie_dir)) {
            $test_path = $roumanie_dir . $filename;
            if (file_exists($test_path) && is_file($test_path)) {
                $file_path = $test_path;
                $found = true;
                break;
            }
        }
        
        // Chercher aussi directement dans le dossier uploads
        $test_path = $base_dir . $filename;
        if (file_exists($test_path) && is_file($test_path)) {
            $file_path = $test_path;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        // Afficher une page d'erreur détaillée
        showErrorPage($filename, $demande);
        exit;
    }
}

// Obtenir les informations du fichier
$file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$file_size = filesize($file_path);
$file_date = date("d/m/Y à H:i", filemtime($file_path));

// Types de fichiers supportés pour la prévisualisation
$supported_preview = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
$can_preview = in_array($file_extension, $supported_preview);

// Si on veut forcer le téléchargement
if (isset($_GET['download'])) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $file_size);
    readfile($file_path);
    exit;
}

// Fonction pour afficher la page d'erreur
function showErrorPage($filename, $demande) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fichier non trouvé - Babylone Service</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            .error-icon {
                font-size: 4rem;
                color: #e74c3c;
                margin-bottom: 20px;
            }
            h1 {
                color: #2c3e50;
                margin-bottom: 15px;
            }
            p {
                color: #7f8c8d;
                margin-bottom: 20px;
                line-height: 1.6;
            }
            .file-info {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: left;
            }
            .info-item {
                margin-bottom: 8px;
            }
            .info-label {
                font-weight: bold;
                color: #34495e;
            }
            .btn {
                display: inline-block;
                background: #3498db;
                color: white;
                padding: 12px 24px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                margin: 5px;
            }
            .btn:hover {
                background: #2980b9;
                transform: translateY(-2px);
            }
            .btn-outline {
                background: transparent;
                border: 2px solid #3498db;
                color: #3498db;
            }
            .btn-outline:hover {
                background: #3498db;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>Fichier non trouvé</h1>
            <p>Le fichier que vous essayez de visualiser n'a pas été trouvé sur le serveur.</p>
            
            <div class="file-info">
                <div class="info-item">
                    <span class="info-label">Nom du fichier :</span> <?php echo htmlspecialchars($filename); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Demande associée :</span> <?php echo htmlspecialchars($demande['specialite']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Type de document :</span> <?php echo htmlspecialchars($demande['document_type']); ?>
                </div>
            </div>
            
            <p>Ce problème peut survenir si :</p>
            <ul style="text-align: left; color: #7f8c8d; margin: 15px 0;">
                <li>Le fichier a été supprimé</li>
                <li>Le fichier n'a pas été correctement uploadé</li>
                <li>Le chemin d'accès au fichier a changé</li>
            </ul>
            
            <div style="margin-top: 25px;">
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="mes_demandes_roumanie.php" class="btn">
                    <i class="fas fa-list"></i> Mes demandes
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation - <?php echo htmlspecialchars($demande['document_type']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgba(0, 43, 127, 0.7);
            --primary-hover: rgba(9, 47, 122, 0.7);
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: #2c3e50;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .header h1 {
            font-size: 1.3rem;
            margin: 0;
        }

        .file-info {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .file-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 2px;
        }

        .info-value {
            font-weight: 500;
        }

        .preview-container {
            flex: 1;
            background: #34495e;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .preview-content {
            max-width: 100%;
            max-height: 100%;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 4px;
            overflow: auto;
        }

        .preview-content img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
        }

        .preview-content object {
            width: 100%;
            height: 80vh;
            border: none;
        }

        .unsupported-file {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: var(--border-radius);
            max-width: 500px;
        }

        .unsupported-file i {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .toolbar {
            background: white;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .zoom-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .zoom-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .zoom-btn:hover {
            background: var(--light-gray);
        }

        .fullscreen-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .fullscreen-btn:hover {
            background: rgba(0,0,0,0.9);
            transform: scale(1.1);
        }

        .fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: rgba(0,0,0,0.95);
        }

        .fullscreen .preview-content {
            max-width: 95vw;
            max-height: 95vh;
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .zoom-controls {
                justify-content: center;
            }
            
            .header h1 {
                font-size: 1.1rem;
            }
            
            .file-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <i class="fas fa-file-alt"></i>
            <?php echo htmlspecialchars($demande['document_type']); ?>
        </h1>
        <button class="btn btn-outline" onclick="window.close()" style="color: white; border-color: white;">
            <i class="fas fa-times"></i> Fermer
        </button>
    </div>

    <div class="file-info">
        <div class="file-info-grid">
            <div class="info-item">
                <span class="info-label">Demande</span>
                <span class="info-value"><?php echo htmlspecialchars($demande['specialite']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Fichier</span>
                <span class="info-value"><?php echo htmlspecialchars($filename); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Taille</span>
                <span class="info-value"><?php echo formatFileSize($file_size); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date</span>
                <span class="info-value"><?php echo $file_date; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Chemin</span>
                <span class="info-value" style="font-size: 0.8rem;"><?php echo htmlspecialchars($file_path); ?></span>
            </div>
        </div>
    </div>

    <div class="preview-container" id="previewContainer">
        <?php if ($can_preview): ?>
            <button class="fullscreen-btn" onclick="toggleFullscreen()" title="Plein écran">
                <i class="fas fa-expand"></i>
            </button>
            
            <div class="preview-content" id="previewContent">
                <?php if ($file_extension === 'pdf'): ?>
                    <object data="<?php echo htmlspecialchars($file_path); ?>#toolbar=1&navpanes=0&scrollbar=1" type="application/pdf">
                        <div class="unsupported-file">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Impossible d'afficher le PDF</h3>
                            <p>Votre navigateur ne supporte pas l'affichage des PDF.</p>
                            <a href="previsualiser_fichier.php?file=<?php echo urlencode($filename); ?>&download=1" class="btn btn-primary">
                                <i class="fas fa-download"></i> Télécharger le PDF
                            </a>
                        </div>
                    </object>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($file_path); ?>" 
                         alt="<?php echo htmlspecialchars($demande['document_type']); ?>"
                         id="previewImage">
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="unsupported-file">
                <i class="fas fa-file"></i>
                <h3>Prévisualisation non disponible</h3>
                <p>Le format .<?php echo strtoupper($file_extension); ?> n'est pas supporté pour la prévisualisation.</p>
                <p class="info-value" style="margin: 10px 0;"><?php echo formatFileSize($file_size); ?></p>
                <a href="previsualiser_fichier.php?file=<?php echo urlencode($filename); ?>&download=1" class="btn btn-primary">
                    <i class="fas fa-download"></i> Télécharger le fichier
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($can_preview): ?>
    <div class="toolbar">
        <div>
            <a href="previsualiser_fichier.php?file=<?php echo urlencode($filename); ?>&download=1" class="btn btn-primary">
                <i class="fas fa-download"></i> Télécharger
            </a>
            <button class="btn btn-outline" onclick="printFile()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
        
        <?php if ($file_extension !== 'pdf'): ?>
        <div class="zoom-controls">
            <button class="zoom-btn" onclick="zoomOut()" title="Zoom arrière">
                <i class="fas fa-search-minus"></i>
            </button>
            <span id="zoomLevel">100%</span>
            <button class="zoom-btn" onclick="zoomIn()" title="Zoom avant">
                <i class="fas fa-search-plus"></i>
            </button>
            <button class="zoom-btn" onclick="resetZoom()" title="Taille originale">
                <i class="fas fa-expand-arrows-alt"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <div>
            <button class="btn btn-outline" onclick="window.close()">
                <i class="fas fa-times"></i> Fermer la fenêtre
            </button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        let zoomLevel = 1;
        const previewImage = document.getElementById('previewImage');
        const zoomLevelElement = document.getElementById('zoomLevel');

        function zoomIn() {
            if (previewImage) {
                zoomLevel += 0.1;
                previewImage.style.transform = `scale(${zoomLevel})`;
                zoomLevelElement.textContent = Math.round(zoomLevel * 100) + '%';
            }
        }

        function zoomOut() {
            if (previewImage && zoomLevel > 0.2) {
                zoomLevel -= 0.1;
                previewImage.style.transform = `scale(${zoomLevel})`;
                zoomLevelElement.textContent = Math.round(zoomLevel * 100) + '%';
            }
        }

        function resetZoom() {
            if (previewImage) {
                zoomLevel = 1;
                previewImage.style.transform = 'scale(1)';
                zoomLevelElement.textContent = '100%';
            }
        }

        function toggleFullscreen() {
            const container = document.getElementById('previewContainer');
            const fullscreenBtn = document.querySelector('.fullscreen-btn i');
            
            if (!document.fullscreenElement) {
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) {
                    container.webkitRequestFullscreen();
                } else if (container.msRequestFullscreen) {
                    container.msRequestFullscreen();
                }
                container.classList.add('fullscreen');
                fullscreenBtn.className = 'fas fa-compress';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                container.classList.remove('fullscreen');
                fullscreenBtn.className = 'fas fa-expand';
            }
        }

        function printFile() {
            window.print();
        }

        // Gestion des touches du clavier
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'Escape':
                    if (document.fullscreenElement) {
                        toggleFullscreen();
                    } else {
                        window.close();
                    }
                    break;
                case '+':
                case '=':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        zoomIn();
                    }
                    break;
                case '-':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        zoomOut();
                    }
                    break;
                case '0':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        resetZoom();
                    }
                    break;
            }
        });
    </script>
</body>
</html>

<?php
// Fonction pour formater la taille des fichiers
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    
    return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>