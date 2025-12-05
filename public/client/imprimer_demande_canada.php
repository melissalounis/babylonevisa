<?php
require_once __DIR__ . '../../../config.php';
require_login();

// Vérifier si un ID de demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de demande manquant.");
}

$demande_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Récupérer la demande spécifique de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("
        SELECT d.*, s.titre as service_titre 
        FROM demandes d 
        LEFT JOIN services s ON d.visa_type = s.titre 
        WHERE d.id = ? AND d.user_id = ? AND d.visa_type LIKE '%Canada%'
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch();
    
    if (!$demande) {
        die("Demande non trouvée ou vous n'avez pas l'autorisation de la consulter.");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération de la demande: " . $e->getMessage());
}

// Fonction pour formater les valeurs
function formatValue($value) {
    return !empty($value) ? htmlspecialchars($value) : '<em>Non renseigné</em>';
}

// Fonction pour formater le niveau d'étude
function formatNiveauEtude($niveau) {
    $niveaux = [
        'bac' => 'Baccalauréat',
        'licence' => 'Licence',
        'master' => 'Master',
        'doctorat' => 'Doctorat',
        'certificat' => 'Certificat/Diplôme'
    ];
    return $niveaux[$niveau] ?? ucfirst($niveau);
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'nouveau' => 'Nouvelle demande',
        'en_traitement' => 'En traitement',
        'accepte' => 'Acceptée',
        'refuse' => 'Refusée'
    ];
    return $statuts[$statut] ?? ucfirst($statut);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression Demande Canada #<?= $demande['id'] ?></title>
    <style>
        @media print {
            @page {
                margin: 1cm;
                size: A4;
            }
            
            body {
                margin: 0;
                padding: 0;
                font-family: 'Arial', sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #000;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            .header, .footer {
                text-align: center;
                margin-bottom: 20px;
            }
            
            .section {
                margin-bottom: 25px;
                page-break-inside: avoid;
            }
            
            .section-title {
                background: #f8f9fa;
                padding: 8px 12px;
                border-left: 4px solid #D80621;
                margin-bottom: 15px;
                font-weight: bold;
                font-size: 14px;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 10px;
            }
            
            .info-item {
                margin-bottom: 8px;
            }
            
            .info-label {
                font-weight: bold;
                color: #555;
                margin-bottom: 3px;
                font-size: 11px;
            }
            
            .info-value {
                padding: 5px 0;
                border-bottom: 1px solid #eee;
            }
            
            .document-list {
                margin-top: 10px;
            }
            
            .document-item {
                padding: 5px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
            }
            
            .status-nouveau { background: #ffc107; color: #000; }
            .status-en_traitement { background: #17a2b8; color: white; }
            .status-accepte { background: #28a745; color: white; }
            .status-refuse { background: #dc3545; color: white; }
            
            .logo {
                text-align: center;
                margin-bottom: 20px;
            }
            
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 80px;
                color: rgba(0,0,0,0.1);
                z-index: -1;
                pointer-events: none;
            }
        }
        
        @media screen {
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f5f5f5;
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
            }
            
            .print-container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .print-actions {
                text-align: center;
                margin-bottom: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
            }
            
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0 5px;
                font-size: 14px;
            }
            
            .btn-primary {
                background: #D80621;
                color: white;
            }
            
            .btn-outline {
                background: transparent;
                border: 2px solid #D80621;
                color: #D80621;
            }
            
            .section {
                margin-bottom: 25px;
            }
            
            .section-title {
                background: #f8f9fa;
                padding: 12px 15px;
                border-left: 4px solid #D80621;
                margin-bottom: 15px;
                font-weight: bold;
                font-size: 16px;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 15px;
            }
            
            .info-item {
                margin-bottom: 12px;
            }
            
            .info-label {
                font-weight: bold;
                color: #555;
                margin-bottom: 5px;
                font-size: 13px;
            }
            
            .info-value {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
            }
            
            .document-list {
                margin-top: 10px;
            }
            
            .document-item {
                padding: 8px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .status-badge {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
            }
            
            .logo {
                text-align: center;
                margin-bottom: 30px;
            }
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #D80621;
        }
        
        .company-info {
            text-align: left;
        }
        
        .demande-info {
            text-align: right;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #D80621;
            margin-bottom: 5px;
        }
        
        .demande-ref {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .date-print {
            color: #666;
            font-size: 12px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .notes-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
        }
        
        .signature-area {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
        }
        
        .signature-line {
            width: 300px;
            border-bottom: 1px solid #000;
            margin: 40px auto 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Actions d'impression (visible seulement à l'écran) -->
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="mes_demandes_canada.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button onclick="window.close()" class="btn btn-outline">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>

        <!-- Filigrane -->
        <div class="watermark no-print">BABYLONE SERVICE</div>

        <!-- En-tête -->
        <div class="header-info">
            <div class="company-info">
                <div class="company-name">BABYLONE SERVICE</div>
                <div>Spécialiste en immigration canadienne</div>
                <div>Email: contact@babyloneservice.com</div>
                <div>Tél: +1 234 567 8900</div>
            </div>
            <div class="demande-info">
                <div class="demande-ref">DEMANDE CANADA #<?= $demande['id'] ?></div>
                <div class="status-badge status-<?= $demande['status'] ?>">
                    <?= formatStatut($demande['status']) ?>
                </div>
                <div class="date-print">
                    Imprimé le: <?= date('d/m/Y à H:i') ?>
                </div>
            </div>
        </div>

        <!-- Informations personnelles -->
        <div class="section">
            <div class="section-title">INFORMATIONS PERSONNELLES</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nom complet</div>
                    <div class="info-value"><?= formatValue($demande['nom_complet']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= formatValue($demande['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value"><?= formatValue($demande['telephone']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Type de visa/demande</div>
                    <div class="info-value"><?= formatValue($demande['visa_type']) ?></div>
                </div>
            </div>
        </div>

        <!-- Informations académiques -->
        <div class="section">
            <div class="section-title">INFORMATIONS ACADÉMIQUES</div>
            <div class="info-grid">
                <?php if (!empty($demande['niveau_etude'])): ?>
                <div class="info-item">
                    <div class="info-label">Niveau d'étude</div>
                    <div class="info-value"><?= formatNiveauEtude($demande['niveau_etude']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($demande['universite_souhaitee'])): ?>
                <div class="info-item">
                    <div class="info-label">Université souhaitée</div>
                    <div class="info-value"><?= formatValue($demande['universite_souhaitee']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($demande['programme_etude'])): ?>
                <div class="info-item">
                    <div class="info-label">Programme d'études</div>
                    <div class="info-value"><?= formatValue($demande['programme_etude']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dates importantes -->
        <div class="section">
            <div class="section-title">DATES IMPORTANTES</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Date de soumission</div>
                    <div class="info-value"><?= date('d/m/Y à H:i', strtotime($demande['created_at'])) ?></div>
                </div>
                <?php if (!empty($demande['updated_at']) && $demande['updated_at'] != $demande['created_at']): ?>
                <div class="info-item">
                    <div class="info-label">Dernière mise à jour</div>
                    <div class="info-value"><?= date('d/m/Y à H:i', strtotime($demande['updated_at'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documents déposés -->
        <div class="section">
            <div class="section-title">DOCUMENTS DÉPOSÉS</div>
            <div class="document-list">
                <?php
                $documents = [
                    'Passeport' => $demande['passeport_pdf_path'],
                    'Relevés de notes' => $demande['releves_notes_path'],
                    'Diplôme' => $demande['diplome_path'],
                    'CV' => $demande['cv_path'],
                    'Lettre de motivation' => $demande['lettre_motivation_path'],
                    'Test linguistique' => $demande['test_linguistique_path'],
                    'Justificatifs financiers' => $demande['justificatif_ressources_path']
                ];
                
                $documentsDeposes = array_filter($documents);
                ?>
                
                <?php if (!empty($documentsDeposes)): ?>
                    <?php foreach ($documentsDeposes as $docName => $docPath): ?>
                        <div class="document-item">
                            <strong><?= $docName ?>:</strong> 
                            <?= basename($docPath) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="document-item">
                        <em>Aucun document déposé</em>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($demande['notes'])): ?>
        <div class="section">
            <div class="section-title">NOTES ET INFORMATIONS COMPLÉMENTAIRES</div>
            <div class="notes-section">
                <?= nl2br(htmlspecialchars($demande['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pied de page avec zone de signature -->
        <div class="signature-area">
            <div class="signature-line"></div>
            <div style="text-align: center; font-size: 11px; color: #666;">
                Signature du responsable Babylone Service
            </div>
            
            <div style="margin-top: 50px; font-size: 10px; color: #999; text-align: center;">
                <strong>BABYLONE SERVICE</strong> - Ce document a été généré électroniquement le <?= date('d/m/Y à H:i') ?><br>
                Référence: CAN-<?= str_pad($demande['id'], 6, '0', STR_PAD_LEFT) ?> - Statut: <?= formatStatut($demande['status']) ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-impression optionnelle
        window.onload = function() {
            // Démarrer l'impression automatiquement après 1 seconde
            setTimeout(function() {
                // window.print(); // Décommentez pour l'impression automatique
            }, 1000);
        };

        // Gestion des boutons
        document.addEventListener('DOMContentLoaded', function() {
            // Ajouter les icônes Font Awesome dynamiquement
            const style = document.createElement('link');
            style.rel = 'stylesheet';
            style.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(style);
        });
    </script>
</body>
</html>