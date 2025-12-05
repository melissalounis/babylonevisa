<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Connexion DB
require_once __DIR__ . '../../../config.php';

try {
    
} catch (Exception $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Récupérer toutes les évaluations de l'utilisateur avec leurs documents
$stmt = $pdo->prepare("
    SELECT 
        e.id as evaluation_id,
        e.programme,
        e.date_soumission,
        e.score,
        e.eligible,
        d.id as document_id,
        d.type_document,
        d.nom_fichier,
        d.chemin_fichier,
        d.taille,
        d.date_upload
    FROM evaluations_immigration e 
    JOIN users u ON e.email = u.email 
    LEFT JOIN documents_immigration d ON e.id = d.evaluation_id
    WHERE u.id = ? 
    ORDER BY e.date_soumission DESC, d.date_upload DESC
");
$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les données par évaluation
$evaluations = [];
$all_documents = [];

foreach ($results as $row) {
    $eval_id = $row['evaluation_id'];
    
    // Ajouter l'évaluation si elle n'existe pas encore
    if (!isset($evaluations[$eval_id])) {
        $evaluations[$eval_id] = [
            'id' => $row['evaluation_id'],
            'programme' => $row['programme'],
            'date_soumission' => $row['date_soumission'],
            'score' => $row['score'],
            'eligible' => $row['eligible'],
            'documents' => []
        ];
    }
    
    // Ajouter le document s'il existe
    if ($row['document_id']) {
        $document = [
            'id' => $row['document_id'],
            'type_document' => $row['type_document'],
            'nom_fichier' => $row['nom_fichier'],
            'chemin_fichier' => $row['chemin_fichier'],
            'taille' => $row['taille'],
            'date_upload' => $row['date_upload'],
            'evaluation_id' => $row['evaluation_id'],
            'programme' => $row['programme']
        ];
        
        $evaluations[$eval_id]['documents'][] = $document;
        $all_documents[] = $document;
    }
}

// Fonction pour formater les noms des documents
function getDocumentLabel($docType) {
    $labels = [
        'passport' => 'Passeport',
        'passeport' => 'Passeport',
        'id_photo' => 'Photo d\'identité',
        'cv' => 'Curriculum Vitae',
        'language_test' => 'Test d\'anglais (IELTS/CELPIP)',
        'tcf_canada' => 'Test de français (TCF Canada)',
        'degree_assessment' => 'Évaluation des diplômes',
        'work_reference' => 'Références professionnelles',
        'french_test' => 'Test de français (TEF/TCF)',
        'quebec_values' => 'Attestation valeurs Québec',
        'quebec_documents' => 'Documents spécifiques Québec',
        'marriage_cert' => 'Certificat de mariage',
        'mariage' => 'Certificat de mariage',
        'spouse_passport' => 'Passeport du conjoint',
        'children_docs' => 'Documents des enfants',
        'family_record' => 'Fiche familiale',
        'birth_certificates' => 'Actes de naissance',
        'naissance' => 'Certificat de naissance',
        'diplomes' => 'Diplômes',
        'eca' => 'Évaluation ECA',
        'experience' => 'Attestations d\'expérience',
        'ielts' => 'Test IELTS',
        'tef' => 'Test TEF',
        'releves_bancaires' => 'Relevés bancaires',
        'offre_emploi' => 'Offre d\'emploi'
    ];
    
    return $labels[$docType] ?? $docType;
}

// Fonction pour formater la taille
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Fonction pour obtenir l'icône du document
function getDocumentIcon($docType) {
    $icons = [
        'passport' => '🛂',
        'passeport' => '🛂',
        'id_photo' => '📷',
        'cv' => '📄',
        'language_test' => '🗣️',
        'tcf_canada' => '🇫🇷',
        'degree_assessment' => '🎓',
        'diplomes' => '🎓',
        'eca' => '🎓',
        'work_reference' => '💼',
        'experience' => '💼',
        'french_test' => '🇫🇷',
        'tef' => '🇫🇷',
        'quebec_values' => '❄️',
        'quebec_documents' => '🏢',
        'marriage_cert' => '💍',
        'mariage' => '💍',
        'spouse_passport' => '👤',
        'children_docs' => '👶',
        'family_record' => '👨‍👩‍👧‍👦',
        'birth_certificates' => '📜',
        'naissance' => '📜',
        'releves_bancaires' => '💰',
        'ielts' => '🇬🇧',
        'offre_emploi' => '💼'
    ];
    
    return $icons[$docType] ?? '📄';
}

// Fonction pour obtenir le nom du programme
function getProgramName($program) {
    $programs = [
        'express' => 'Entrée Express',
        'arrima' => 'Arrima Québec'
    ];
    return $programs[$program] ?? $program;
}

// Statistiques globales
$total_documents = count($all_documents);
$total_size = array_sum(array_column($all_documents, 'taille'));
$programs_count = [];
foreach ($evaluations as $eval) {
    $program = $eval['programme'];
    if (!isset($programs_count[$program])) {
        $programs_count[$program] = 0;
    }
    $programs_count[$program] += count($eval['documents']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Documents - Immigration Canada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .header p {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 24px;
        }

        .badge {
            display: inline-block;
            background: #e2e8f0;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            margin: 0 4px;
        }

        .badge-program {
            background: #3b82f6;
            color: white;
        }

        .badge-express {
            background: #dc2626;
            color: white;
        }

        .badge-arrima {
            background: #059669;
            color: white;
        }

        /* Statistiques */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Navigation */
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .nav-btn {
            padding: 12px 24px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            color: #374151;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        /* Sections par programme */
        .program-section {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }

        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .program-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .program-info {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .program-date {
            color: #64748b;
            font-size: 0.9rem;
        }

        .program-score {
            background: #f0f9ff;
            color: #0369a1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Liste des documents */
        .documents-list {
            space-y: 16px;
        }

        .document-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .document-item:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }

        .document-icon {
            font-size: 2rem;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .document-details {
            flex: 1;
        }

        .document-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .document-meta {
            display: flex;
            gap: 16px;
            font-size: 0.875rem;
            color: #64748b;
        }

        .document-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-outline {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            border: 1px solid #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: #475569;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        /* Alertes */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 16px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .program-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .document-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .document-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .document-meta {
                flex-direction: column;
                gap: 4px;
            }

            .navigation {
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mes Documents d'Immigration</h1>
            <p>Consultez tous vos documents pour les programmes Entrée Express et Arrima Québec</p>
            <div>
                <span class="badge">ID Utilisateur : #<?= $user_id ?></span>
                <span class="badge badge-express">Entrée Express</span>
                <span class="badge badge-arrima">Arrima Québec</span>
            </div>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <a href="documents.php" class="nav-btn">
                📤 Uploader des documents
            </a>
            <a href="mes_demandes_immigration.php" class="nav-btn">
                📋 Mes demandes
            </a>
        </div>

        <!-- Statistiques globales -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_documents ?></div>
                <div class="stat-label">Total des documents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= formatFileSize($total_size) ?></div>
                <div class="stat-label">Espace utilisé</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($evaluations) ?></div>
                <div class="stat-label">Évaluations effectuées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($programs_count) ?></div>
                <div class="stat-label">Programmes utilisés</div>
            </div>
        </div>

        <!-- Affichage par programme -->
        <?php if (empty($evaluations)): ?>
            <div class="empty-state">
                <div class="icon">📁</div>
                <h3>Aucune évaluation trouvée</h3>
                <p>Vous n'avez pas encore effectué d'évaluation d'immigration.</p>
                <a href="index.php" class="btn btn-primary">
                    🚀 Commencer une évaluation
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($evaluations as $evaluation): ?>
                <div class="program-section">
                    <div class="program-header">
                        <div class="program-title">
                            <?php if ($evaluation['programme'] === 'express'): ?>
                                🍁 <?= getProgramName($evaluation['programme']) ?>
                            <?php else: ?>
                                ❄️ <?= getProgramName($evaluation['programme']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="program-info">
                            <span class="program-date">
                                Évalué le <?= date('d/m/Y', strtotime($evaluation['date_soumission'])) ?>
                            </span>
                            <span class="program-score">
                                Score: <?= $evaluation['score'] ?> points
                            </span>
                            <span class="badge <?= $evaluation['programme'] === 'express' ? 'badge-express' : 'badge-arrima' ?>">
                                <?= $evaluation['eligible'] ? '✅ Éligible' : '❌ Non éligible' ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($evaluation['documents'])): ?>
                        <div class="empty-state">
                            <div class="icon">📄</div>
                            <h3>Aucun document pour cette évaluation</h3>
                            <p>Vous n'avez pas encore uploadé de documents pour cette demande.</p>
                            <a href="documents.php" class="btn btn-primary">
                                📤 Uploader des documents
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            📊 <?= count($evaluation['documents']) ?> document(s) uploadé(s) pour cette évaluation
                        </div>

                        <div class="documents-list">
                            <?php foreach ($evaluation['documents'] as $doc): ?>
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon">
                                            <?= getDocumentIcon($doc['type_document']) ?>
                                        </div>
                                        <div class="document-details">
                                            <div class="document-name">
                                                <?= getDocumentLabel($doc['type_document']) ?>
                                            </div>
                                            <div class="document-meta">
                                                <span><strong>Fichier :</strong> <?= htmlspecialchars($doc['nom_fichier']) ?></span>
                                                <span><strong>Taille :</strong> <?= formatFileSize($doc['taille']) ?></span>
                                                <span><strong>Uploadé le :</strong> <?= date('d/m/Y à H:i', strtotime($doc['date_upload'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="document-actions">
                                        <a href="<?= $doc['chemin_fichier'] ?>" target="_blank" class="btn btn-outline">
                                            👁️ Voir
                                        </a>
                                        <a href="download_document.php?id=<?= $doc['id'] ?>" class="btn btn-outline">
                                            📥 Télécharger
                                        </a>
                                        <a href="delete_document.php?id=<?= $doc['id'] ?>" class="btn btn-danger" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                            🗑️ Supprimer
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Animation des cartes
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.document-item');
            items.forEach((item, index) => {
                item.style.animationDelay = (index * 0.1) + 's';
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.4s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });

            // Animation des sections programme
            const sections = document.querySelectorAll('.program-section');
            sections.forEach((section, index) => {
                section.style.animationDelay = (index * 0.2) + 's';
                section.style.opacity = '0';
                section.style.transform = 'translateX(-30px)';
                
                setTimeout(() => {
                    section.style.transition = 'all 0.5s ease-out';
                    section.style.opacity = '1';
                    section.style.transform = 'translateX(0)';
                }, 200 + (index * 200));
            });

            // Confirmation avant suppression
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-danger')) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>