<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Paiement - Administration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 25px 0; text-align: center; border-radius: 10px; margin-bottom: 30px; }
        .btn { padding: 10px 20px; background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn:hover { background: linear-gradient(135deg, #2a5298, #3a6bd1); transform: translateY(-2px); }
        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .btn-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        .btn-danger { background: linear-gradient(135deg, #dc3545, #e83e8c); }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        .detail-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); margin-bottom: 30px; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .detail-section { margin-bottom: 30px; }
        .detail-section h3 { color: #2a5298; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        .detail-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #333; text-align: right; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-paye { background: #d1edff; color: #004085; }
        .status-confirme { background: #d4edda; color: #155724; }
        .status-annule { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { margin-bottom: 20px; }
        .payment-proof { margin-top: 20px; text-align: center; }
        .payment-proof img { max-width: 100%; max-height: 500px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        .no-proof { color: #666; font-style: italic; padding: 20px; background: #f8f9fa; border-radius: 6px; }
        .proof-actions { display: flex; gap: 10px; justify-content: center; margin-top: 15px; }
        .file-info { background: #f8f9fa; padding: 10px; border-radius: 6px; margin-top: 10px; font-size: 0.9rem; }
        .file-path { word-break: break-all; font-family: monospace; background: #e9ecef; padding: 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="paiement.php" class="btn">&larr; Retour à l'administration</a>
        </div>

        <header>
            <h1>Détails du Paiement</h1>
            <p class="subtitle">Informations complètes de la demande</p>
        </header>

        <?php
        // Connexion à la base de données
        $host = 'localhost';
        $dbname = 'babylone_Service';
        $username = 'root';
        $password = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifier si la référence est fournie
            if (!isset($_GET['reference']) || empty($_GET['reference'])) {
                throw new Exception("Aucune référence de paiement spécifiée.");
            }

            $reference = $_GET['reference'];

            // Récupérer les détails du paiement
            $stmt = $pdo->prepare("SELECT * FROM paiements WHERE reference_demande = ?");
            $stmt->execute([$reference]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paiement) {
                throw new Exception("Paiement non trouvé pour la référence: " . htmlspecialchars($reference));
            }

            // Traitement de la confirmation du paiement
            if (isset($_POST['confirmer_paiement'])) {
                $nouveau_statut = 'confirme';
                $stmt = $pdo->prepare("UPDATE paiements SET statut = ? WHERE reference_demande = ?");
                $stmt->execute([$nouveau_statut, $reference]);
                
                echo '<div class="alert alert-success">Paiement confirmé avec succès!</div>';
                
                // Recharger les données
                $stmt = $pdo->prepare("SELECT * FROM paiements WHERE reference_demande = ?");
                $stmt->execute([$reference]);
                $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Traitement de l'annulation du paiement
            if (isset($_POST['annuler_paiement'])) {
                $nouveau_statut = 'annule';
                $stmt = $pdo->prepare("UPDATE paiements SET statut = ? WHERE reference_demande = ?");
                $stmt->execute([$nouveau_statut, $reference]);
                
                echo '<div class="alert alert-success">Paiement annulé avec succès!</div>';
                
                // Recharger les données
                $stmt = $pdo->prepare("SELECT * FROM paiements WHERE reference_demande = ?");
                $stmt->execute([$reference]);
                $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
            }

        } catch (Exception $e) {
            echo '<div class="alert alert-error">Erreur: ' . $e->getMessage() . '</div>';
            $paiement = null;
        }
        ?>

        <?php if ($paiement): ?>
        <div class="detail-card">
            <div class="detail-grid">
                <div class="detail-section">
                    <h3>Informations Client</h3>
                    <div class="detail-item">
                        <span class="detail-label">Nom Complet:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['nom_complet']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['telephone']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['adresse'] ?? 'Non spécifiée'); ?></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Informations Paiement</h3>
                    <div class="detail-item">
                        <span class="detail-label">Référence:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['reference_demande']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Montant:</span>
                        <span class="detail-value"><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> DZD</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Service:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paiement['service'] ?? 'Non spécifié'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Statut:</span>
                        <span class="detail-value">
                            <span class="status-badge status-<?php echo $paiement['statut']; ?>">
                                <?php echo str_replace('_', ' ', $paiement['statut']); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Dates</h3>
                <div class="detail-item">
                    <span class="detail-label">Date de Demande:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i:s', strtotime($paiement['date_demande'])); ?></span>
                </div>
                <?php if ($paiement['date_paiement']): ?>
                <div class="detail-item">
                    <span class="detail-label">Date de Paiement:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i:s', strtotime($paiement['date_paiement'])); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($paiement['informations_supplementaires'])): ?>
            <div class="detail-section">
                <h3>Informations Supplémentaires</h3>
                <div class="detail-item">
                    <span class="detail-value" style="text-align: left; width: 100%;">
                        <?php echo nl2br(htmlspecialchars($paiement['informations_supplementaires'])); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section Preuve de Paiement CORRIGÉE -->
            <div class="detail-section">
                <h3>Preuve de Paiement</h3>
                <div class="payment-proof">
                    <?php 
                    $preuve_paiement = $paiement['preuve_paiement'] ?? '';
                    
                    if (!empty($preuve_paiement)): 
                        // Déterminer le chemin correct pour l'image
                        $image_path = $preuve_paiement;
                        
                        // Si c'est un chemin relatif, vérifier s'il commence par uploads/
                        if (strpos($preuve_paiement, 'uploads/') === 0) {
                            $image_path = $preuve_paiement;
                        } elseif (strpos($preuve_paiement, '../') === 0) {
                            // Si le chemin contient ../, le rendre absolu
                            $image_path = str_replace('../', '', $preuve_paiement);
                        }
                        
                        // Vérifier si le fichier existe
                        $file_exists = file_exists($image_path);
                    ?>
                        <div class="file-info">
                            <strong>Chemin du fichier :</strong>
                            <div class="file-path"><?php echo htmlspecialchars($preuve_paiement); ?></div>
                            <div style="margin-top: 5px;">
                                <strong>Statut :</strong> 
                                <span style="color: <?php echo $file_exists ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $file_exists ? 'Fichier trouvé ✓' : 'Fichier non trouvé ✗'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($file_exists): ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 alt="Preuve de paiement pour la référence <?php echo htmlspecialchars($paiement['reference_demande']); ?>"
                                 onerror="this.style.display='none'; document.getElementById('image-error').style.display='block';"
                                 style="max-width: 100%; max-height: 500px; border-radius: 8px; border: 1px solid #ddd;">
                            
                            <div id="image-error" class="no-proof" style="display: none;">
                                ❌ Erreur lors du chargement de l'image
                            </div>
                            
                            <div class="proof-actions">
                                <a href="<?php echo htmlspecialchars($image_path); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm">
                                    🔍 Voir en grand
                                </a>
                                <a href="<?php echo htmlspecialchars($image_path); ?>" 
                                   download="preuve_paiement_<?php echo htmlspecialchars($paiement['reference_demande']); ?>.jpg" 
                                   class="btn btn-sm btn-success">
                                    📥 Télécharger
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="no-proof">
                                ❌ Le fichier de preuve n'existe pas sur le serveur.<br>
                                <small>Chemin recherché : <?php echo htmlspecialchars($image_path); ?></small>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="no-proof">
                            📄 Aucune preuve de paiement fournie par le client
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Administrateur -->
            <div class="detail-section">
                <h3>Actions</h3>
                <div class="action-buttons">
                    <?php if ($paiement['statut'] == 'paye'): ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="confirmer_paiement" class="btn btn-success">
                                ✓ Confirmer le Paiement
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($paiement['statut'] != 'annule'): ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="annuler_paiement" class="btn btn-danger" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler ce paiement ?')">
                                ✗ Annuler le Paiement
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="paiement.php" class="btn">Retour à la liste</a>
                    
                    <?php if ($paiement['statut'] == 'en_attente'): ?>
                        <a href="confirmer_paiement.php?reference=<?php echo $paiement['reference_demande']; ?>" 
                           class="btn btn-warning">
                            Marquer comme Payé
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Debug pour voir les informations de la preuve
        <?php if ($paiement && !empty($paiement['preuve_paiement'])): ?>
            console.log('Informations preuve de paiement:');
            console.log('- Référence:', '<?php echo $paiement['reference_demande']; ?>');
            console.log('- Chemin dans la base:', '<?php echo $paiement['preuve_paiement']; ?>');
            console.log('- Chemin utilisé:', '<?php echo $image_path ?? ''; ?>');
            console.log('- Fichier existe:', <?php echo $file_exists ?? 'false'; ?>);
        <?php endif; ?>
    </script>
</body>
</html>