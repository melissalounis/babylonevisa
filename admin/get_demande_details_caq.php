<?php
// get_demande_details_caq.php - Version corrigée pour vos champs
session_start();

// Vérifier si admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('<div class="alert alert-error">Accès non autorisé</div>');
}

$config_path = __DIR__ . '/../config.php';
if (!file_exists($config_path)) {
    die('<div class="alert alert-error">Fichier de configuration non trouvé</div>');
}

include $config_path;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div class="alert alert-error">ID de demande invalide</div>');
}

$id = (int)$_GET['id'];

try {
    // Récupérer les détails
    $sql = "SELECT * FROM demandes_caq WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die('<div class="alert alert-error">Demande CAQ non trouvée</div>');
    }

    // Statuts traduits
    $statuts = [
        'nouveau' => 'Nouveau',
        'en_cours' => 'En cours',
        'approuve' => 'Approuvé',
        'rejete' => 'Rejeté'
    ];
    
    // VOS CHAMPS DE FICHIERS (selon ce que vous avez montré)
    $champs_fichiers = [
        'photos_identite_path' => 'Photos Identité',
        'releves_bancaires_path' => 'Relevés Bancaires', 
        'diplomes_path' => 'Diplômes',
        'test_francais_path' => 'Test Français',
        'autres_documents_path' => 'Autres Documents'
    ];
    
    $fichiers = [];
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/../';
    $uploads_base = 'uploads/caq/';
    
    foreach ($champs_fichiers as $champ => $label) {
        if (isset($demande[$champ]) && !empty($demande[$champ])) {
            // Décoder le JSON
            $chemins = json_decode($demande[$champ], true);
            
            // Si c'est un tableau JSON
            if (is_array($chemins)) {
                foreach ($chemins as $index => $chemin) {
                    if (!empty($chemin)) {
                        $chemin_relatif = $uploads_base . $chemin;
                        $chemin_absolu = $_SERVER['DOCUMENT_ROOT'] . '/' . $chemin_relatif;
                        
                        // Construire l'URL correctement
                        $url_fichier = $base_url . $chemin_relatif;
                        
                        $fichiers[] = [
                            'type' => $label,
                            'chemin' => $chemin,
                            'chemin_relatif' => $chemin_relatif,
                            'chemin_absolu' => $chemin_absolu,
                            'url' => $url_fichier,
                            'extension' => strtolower(pathinfo($chemin, PATHINFO_EXTENSION))
                        ];
                    }
                }
            } 
            // Si c'est une chaîne simple (pas JSON)
            else if (is_string($demande[$champ]) && !empty($demande[$champ])) {
                $chemin = $demande[$champ];
                $chemin_relatif = $uploads_base . $chemin;
                $chemin_absolu = $_SERVER['DOCUMENT_ROOT'] . '/' . $chemin_relatif;
                $url_fichier = $base_url . $chemin_relatif;
                
                $fichiers[] = [
                    'type' => $label,
                    'chemin' => $chemin,
                    'chemin_relatif' => $chemin_relatif,
                    'chemin_absolu' => $chemin_absolu,
                    'url' => $url_fichier,
                    'extension' => strtolower(pathinfo($chemin, PATHINFO_EXTENSION))
                ];
            }
        }
    }
    
    // Afficher les détails pour débogage
    // echo '<pre>'; print_r($demande); echo '</pre>';
    ?>
    
    <div class="detail-grid">
        <!-- Informations de base -->
        <div class="detail-section">
            <h4><i class="fas fa-info-circle"></i> Informations de la demande</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">ID</div>
                    <div class="detail-value">CAQ<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <!-- Afficher les champs textuels -->
                <?php 
                $champs_textuels = [
                    'nom_complet', 'email', 'telephone', 'date_naissance',
                    'lieu_naissance', 'nationalite', 'pays_origine',
                    'programme_etudes', 'institution', 'niveau_etudes'
                ];
                
                foreach ($champs_textuels as $champ): 
                    if (isset($demande[$champ]) && !empty($demande[$champ])): 
                        $label = str_replace('_', ' ', $champ);
                        $label = ucwords($label);
                        $value = $demande[$champ];
                        
                        // Formater les dates
                        if (strpos($champ, 'date_') === 0) {
                            $value = date('d/m/Y', strtotime($value));
                        }
                ?>
                <div class="detail-item">
                    <div class="detail-label"><?php echo htmlspecialchars($label); ?></div>
                    <div class="detail-value"><?php echo htmlspecialchars($value); ?></div>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
                
                <!-- Statut -->
                <div class="detail-item">
                    <div class="detail-label">Statut</div>
                    <div class="detail-value">
                        <span class="badge badge-<?php echo $demande['statut'] ?? 'nouveau'; ?>">
                            <?php echo isset($statuts[$demande['statut']]) ? $statuts[$demande['statut']] : ($demande['statut'] ?? 'Nouveau'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Date de soumission -->
                <?php if (isset($demande['date_soumission']) && !empty($demande['date_soumission'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Date de soumission</div>
                    <div class="detail-value">
                        <?php echo date('d/m/Y H:i:s', strtotime($demande['date_soumission'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documents avec prévisualisation -->
        <?php if (!empty($fichiers)): ?>
        <div class="detail-section">
            <h4><i class="fas fa-file-alt"></i> Documents joints</h4>
            <div class="debug-info">
                <strong>Base URL:</strong> <?php echo $base_url; ?><br>
                <strong>Dossier uploads:</strong> <?php echo $uploads_base; ?>
            </div>
            <div class="files-grid">
                <?php foreach ($fichiers as $fichier): 
                    $exists = file_exists($fichier['chemin_absolu']);
                    $file_size = $exists ? filesize($fichier['chemin_absolu']) : 0;
                    $file_size_formatted = formatFileSize($file_size);
                    $icon = getFileIcon($fichier['extension']);
                    $can_preview = canPreviewFile($fichier['extension']);
                ?>
                <div class="file-card">
                    <div class="file-icon" style="font-size: 2.5rem; color: <?php echo getFileColor($fichier['extension']); ?>">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="file-name">
                        <strong><?php echo htmlspecialchars($fichier['type']); ?></strong><br>
                        <small><?php echo htmlspecialchars($fichier['chemin']); ?></small><br>
                        <small style="color: #666;"><?php echo $file_size_formatted; ?></small><br>
                        <small style="color: <?php echo $exists ? 'green' : 'red'; ?>;">
                            <?php echo $exists ? '✓ Disponible' : '✗ Fichier non trouvé'; ?>
                        </small>
                    </div>
                    <div class="file-actions">
                        <?php if ($exists): ?>
                            <!-- Bouton Voir (prévisualisation) -->
                            <?php if ($can_preview): ?>
                            <button onclick="previewFile('<?php echo htmlspecialchars($fichier['url']); ?>', '<?php echo htmlspecialchars($fichier['type']); ?>')" 
                                    class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </button>
                            <?php endif; ?>
                            
                            <!-- Bouton Télécharger -->
                            <a href="<?php echo htmlspecialchars($fichier['url']); ?>" 
                               target="_blank" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Télé.
                            </a>
                        <?php else: ?>
                            <span class="btn btn-danger btn-sm" disabled>
                                <i class="fas fa-times"></i> Indisponible
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="detail-section">
            <h4><i class="fas fa-file-alt"></i> Documents joints</h4>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun document joint à cette demande.
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="detail-section">
            <h4><i class="fas fa-cog"></i> Actions</h4>
            <div class="action-buttons">
                <form method="POST" action="admin_demandes_caq.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                    <input type="hidden" name="action" value="changer_statut">
                    <select name="nouveau_statut" onchange="this.form.submit()" class="status-select">
                        <option value="nouveau" <?php echo ($demande['statut'] ?? '') == 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                        <option value="en_cours" <?php echo ($demande['statut'] ?? '') == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="approuve" <?php echo ($demande['statut'] ?? '') == 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                        <option value="rejete" <?php echo ($demande['statut'] ?? '') == 'rejete' ? 'selected' : ''; ?>>Rejeté</option>
                    </select>
                </form>
                
                <form method="POST" action="admin_demandes_caq.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Supprimer cette demande ?')">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
                
                <?php if (!empty($fichiers)): ?>
                <button onclick="downloadAllFiles()" class="btn btn-success">
                    <i class="fas fa-download"></i> Tous les fichiers
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal pour la prévisualisation -->
    <div id="previewModal" class="modal">
        <div class="modal-content large">
            <span class="close" onclick="closePreview()">&times;</span>
            <h3 id="previewTitle">Prévisualisation</h3>
            <div id="previewContent" style="margin-top: 20px; height: 70vh; overflow: auto;">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div style="margin-top: 20px; text-align: center;">
                <button onclick="closePreview()" class="btn btn-secondary">Fermer</button>
                <a id="downloadLink" href="#" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Fonction améliorée pour prévisualiser les fichiers
    function previewFile(fileUrl, fileName) {
        console.log('Tentative de prévisualisation:', fileUrl);
        
        const modal = document.getElementById('previewModal');
        const title = document.getElementById('previewTitle');
        const content = document.getElementById('previewContent');
        const downloadLink = document.getElementById('downloadLink');
        
        title.textContent = fileName;
        downloadLink.href = fileUrl;
        
        // Afficher le chargement
        content.innerHTML = `
            <div style="text-align: center; padding: 50px;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Chargement du fichier...</p>
                <small>${fileUrl}</small>
            </div>`;
        
        modal.style.display = 'block';
        
        // Tester l'accès au fichier
        testFileAccess(fileUrl)
            .then(isAccessible => {
                if (!isAccessible) {
                    content.innerHTML = `
                        <div class="alert alert-error" style="margin: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Fichier inaccessible</strong><br>
                            Impossible d'accéder au fichier. Vérifiez:<br>
                            1. Que le fichier existe sur le serveur<br>
                            2. Les permissions du dossier uploads<br>
                            3. L'URL complète: ${fileUrl}
                        </div>`;
                    return;
                }
                
                // Déterminer le type de fichier
                const extension = getFileExtension(fileUrl);
                
                switch(extension) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        // Images
                        content.innerHTML = `
                            <div style="text-align: center;">
                                <img src="${fileUrl}" 
                                     style="max-width: 100%; max-height: 65vh;" 
                                     alt="${fileName}"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'alert alert-error\\'>Impossible de charger l\\'image</div>'">
                            </div>`;
                        break;
                        
                    case 'pdf':
                        // PDF - méthode la plus fiable
                        content.innerHTML = `
                            <div style="width: 100%; height: 70vh;">
                                <iframe src="${fileUrl}#toolbar=0&navpanes=0" 
                                        style="width: 100%; height: 100%; border: none;" 
                                        title="PDF: ${fileName}">
                                    Votre navigateur ne supporte pas l'affichage de PDF.
                                    <a href="${fileUrl}" target="_blank">Télécharger le PDF</a>
                                </iframe>
                            </div>`;
                        break;
                        
                    case 'txt':
                    case 'csv':
                        // Fichiers texte
                        fetch(fileUrl)
                            .then(response => response.text())
                            .then(text => {
                                content.innerHTML = `
                                    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                                        <pre style="margin: 0; white-space: pre-wrap; font-family: monospace;">${escapeHtml(text)}</pre>
                                    </div>`;
                            })
                            .catch(error => {
                                content.innerHTML = `<div class="alert alert-error">Erreur: ${error.message}</div>`;
                            });
                        break;
                        
                    default:
                        // Autres types
                        content.innerHTML = `
                            <div style="text-align: center; padding: 20px;">
                                <i class="fas fa-file fa-3x" style="color: #666;"></i>
                                <p>Ce format (.${extension}) ne peut pas être prévisualisé.</p>
                                <p>Utilisez le bouton "Télécharger".</p>
                            </div>`;
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        Erreur: ${error.message}
                    </div>`;
            });
    }
    
    // Tester si un fichier est accessible
    function testFileAccess(url) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('HEAD', url);
            xhr.onload = () => resolve(xhr.status === 200);
            xhr.onerror = () => resolve(false);
            xhr.send();
        });
    }
    
    // Obtenir l'extension d'un fichier
    function getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase().split('?')[0];
    }
    
    // Télécharger tous les fichiers
    function downloadAllFiles() {
        <?php foreach ($fichiers as $fichier): ?>
        window.open('<?php echo htmlspecialchars($fichier['url']); ?>', '_blank');
        <?php endforeach; ?>
    }
    
    function closePreview() {
        document.getElementById('previewModal').style.display = 'none';
    }
    
    // Fermer la modal en cliquant en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('previewModal');
        if (event.target === modal) {
            closePreview();
        }
    }
    
    // Échapper le HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
    
    <style>
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .status-select {
        padding: 8px 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
        background: white;
    }
    
    .file-actions {
        display: flex;
        gap: 5px;
        justify-content: center;
        margin-top: 10px;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
    }
    
    .modal-content.large {
        background: white;
        margin: 2% auto;
        padding: 20px;
        width: 90%;
        max-width: 1200px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 10px;
        position: relative;
    }
    
    .debug-info {
        background: #f0f7ff;
        border-left: 4px solid #4a90e2;
        padding: 10px;
        margin-bottom: 15px;
        font-size: 12px;
        border-radius: 4px;
    }
    </style>
    
    <?php
} catch (PDOException $e) {
    echo '<div class="alert alert-error">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Fonctions utilitaires
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function getFileIcon($extension) {
    $icons = [
        'pdf' => 'fa-file-pdf',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'txt' => 'fa-file-alt',
        'csv' => 'fa-file-csv'
    ];
    return $icons[$extension] ?? 'fa-file';
}

function getFileColor($extension) {
    $colors = [
        'pdf' => '#e74c3c',
        'jpg' => '#e67e22',
        'jpeg' => '#e67e22',
        'png' => '#3498db',
        'gif' => '#2ecc71',
        'doc' => '#2980b9',
        'docx' => '#2980b9',
        'txt' => '#7f8c8d'
    ];
    return $colors[$extension] ?? '#95a5a6';
}

function canPreviewFile($extension) {
    $previewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv'];
    return in_array($extension, $previewable);
}
?>