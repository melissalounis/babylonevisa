<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Récupérer l'email de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé.");
}

$user_email = $user['email'];

// Traitement de l'annulation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_rendezvous'])) {
    $rendezvous_id = $_POST['rendezvous_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE langue_tests SET statut = 'annule' WHERE id = ? AND email = ?");
        $stmt->execute([$rendezvous_id, $user_email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Le rendez-vous a été annulé avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'annulation du rendez-vous.";
        }
        
        header("Location: mes_rendezvous_test_langue.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
        header("Location: mes_rendezvous_test_langue.php");
        exit;
    }
}

// Récupérer tous les rendez-vous de l'utilisateur
$stmt = $pdo->prepare("
    SELECT * FROM langue_tests 
    WHERE email = ? 
    ORDER BY date_rendezvous DESC, heure_rendezvous DESC
");
$stmt->execute([$user_email]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour formater le nom du test
function getTestLabel($testType) {
    $tests = [
        // Tests de Français
        'tcf_tp' => 'TCF Tout Public',
        'tcf_dap' => 'TCF DAP',
        'tcf_anf' => 'TCF ANF',
        'tcf_canada' => 'TCF Canada',
        'tcf_quebec' => 'TCF Québec',
        'delf_a1' => 'DELF A1',
        'delf_a2' => 'DELF A2',
        'delf_b1' => 'DELF B1',
        'delf_b2' => 'DELF B2',
        'dalf_c1' => 'DALF C1',
        'dalf_c2' => 'DALF C2',
        'tef_canada' => 'TEF Canada',
        'tef_quebec' => 'TEF Québec',
        
        // Tests d'Anglais
        'ielts_academic' => 'IELTS Academic',
        'ielts_general' => 'IELTS General Training',
        'toefl_ibt' => 'TOEFL iBT',
        'toeic' => 'TOEIC',
        'cambridge_b2' => 'Cambridge B2 First',
        'cambridge_c1' => 'Cambridge C1 Advanced',
        'celpip_general' => 'CELPIP General',
        'pte_academic' => 'PTE Academic'
    ];
    
    return $tests[$testType] ?? $testType;
}

// Fonction pour formater le type de pièce
function getPieceLabel($pieceType) {
    $pieces = [
        'passeport' => 'Passeport',
        'carte_identite' => 'Carte d\'identité',
        'permis_conduire' => 'Permis de conduire'
    ];
    
    return $pieces[$pieceType] ?? $pieceType;
}

// Fonction pour formater le statut
function getStatusLabel($status) {
    $statuses = [
        'en_attente' => 'En attente',
        'confirme' => 'Confirmé',
        'annule' => 'Annulé',
        'termine' => 'Terminé'
    ];
    
    return $statuses[$status] ?? $status;
}

// Fonction pour obtenir la couleur du statut
function getStatusColor($status) {
    $colors = [
        'en_attente' => '#f59e0b', // orange
        'confirme' => '#10b981',   // vert
        'annule' => '#ef4444',     // rouge
        'termine' => '#3b82f6'     // bleu
    ];
    
    return $colors[$status] ?? '#6b7280';
}

// Fonction pour obtenir l'icône du statut
function getStatusIcon($status) {
    $icons = [
        'en_attente' => '<i class="fas fa-clock"></i>',
        'confirme' => '<i class="fas fa-check-circle"></i>',
        'annule' => '<i class="fas fa-times-circle"></i>',
        'termine' => '<i class="fas fa-flag-checkered"></i>'
    ];
    
    return $icons[$status] ?? '<i class="fas fa-file"></i>';
}

// Fonction pour formater la date en français
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Fonction pour formater l'heure
function formatTime($time) {
    return date('H:i', strtotime($time));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous Tests de Langue</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #334155;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 25px;
        }

        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0 8px;
            backdrop-filter: blur(10px);
        }

        /* Messages d'alerte */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
        }

        /* Navigation */
        .navigation {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 12px 25px;
            border: 2px solid white;
            border-radius: 10px;
            background: transparent;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-btn:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
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
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Liste des rendez-vous */
        .rendezvous-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            font-size: 2rem;
            color: #667eea;
        }

        .rendezvous-list {
            space-y: 25px;
        }

        .rendezvous-card {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #f8fafc;
            margin-bottom: 25px;
        }

        .rendezvous-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .test-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .test-icon {
            font-size: 1.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .test-details h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .test-type {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-content {
            padding: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-group {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #667eea;
            width: 20px;
        }

        .info-value {
            color: #1e293b;
            font-size: 1rem;
        }

        .date-time {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #bae6fd;
        }

        .date-time .info-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0369a1;
        }

        .card-actions {
            display: flex;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline {
            background: white;
            border: 2px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            border: 2px solid #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border: 2px solid #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
            border: 2px solid #f59e0b;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #667eea;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: #475569;
        }

        .empty-state p {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        /* Fichiers */
        .files-section {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .files-title {
            font-weight: 600;
            color: #475569;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .files-list {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            transition: all 0.3s ease;
        }

        .file-item:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            transform: translateY(-2px);
        }

        .file-icon {
            color: #667eea;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 2.2rem;
            }

            .navigation {
                flex-direction: column;
                align-items: center;
            }

            .nav-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .rendezvous-section {
                padding: 25px;
            }

            .card-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .card-actions {
                flex-direction: column;
            }

            .files-list {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Mes Rendez-vous Tests de Langue</h1>
            <p>Gérez tous vos rendez-vous pour les tests de langue</p>
            <div>
                <span class="badge"><i class="fas fa-user"></i> Utilisateur : <?= htmlspecialchars($user_email) ?></span>
                <span class="badge"><i class="fas fa-list"></i> Total : <?= count($rendezvous) ?> rendez-vous</span>
            </div>
        </div>

        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success_message'] ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="navigation">
            <a href="../test_de_langue.php" class="nav-btn">
                <i class="fas fa-plus-circle"></i> Nouveau rendez-vous
            </a>
          
            <a href="index.php" class="nav-btn">
                <i class="fas fa-home"></i> Tableau de bord
            </a>
        </div>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($rendezvous) ?></div>
                <div class="stat-label"><i class="fas fa-calendar-check"></i> Total des rendez-vous</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?= count(array_filter($rendezvous, function($r) { return $r['statut'] === 'en_attente'; })) ?>
                </div>
                <div class="stat-label"><i class="fas fa-clock"></i> En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?= count(array_filter($rendezvous, function($r) { return $r['statut'] === 'confirme'; })) ?>
                </div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Confirmés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?= count(array_filter($rendezvous, function($r) { return in_array($r['statut'], ['termine', 'annule']); })) ?>
                </div>
                <div class="stat-label"><i class="fas fa-archive"></i> Clôturés</div>
            </div>
        </div>

        <!-- Liste des rendez-vous -->
        <div class="rendezvous-section">
            <h2 class="section-title">
                <i class="fas fa-list-alt"></i> Mes rendez-vous programmés
            </h2>
            
            <?php if (empty($rendezvous)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fas fa-calendar-times"></i></div>
                    <h3>Aucun rendez-vous trouvé</h3>
                    <p>Vous n'avez pas encore pris de rendez-vous pour un test de langue.</p>
                    <a href="../test_de_langue.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1rem;">
                        <i class="fas fa-plus-circle"></i> Prendre un rendez-vous
                    </a>
                </div>
            <?php else: ?>
                <div class="rendezvous-list">
                    <?php foreach ($rendezvous as $rdv): ?>
                        <div class="rendezvous-card">
                            <div class="card-header">
                                <div class="test-info">
                                    <div class="test-icon">
                                        <?php if (strpos($rdv['type_test'], 'ielts') !== false || strpos($rdv['type_test'], 'toefl') !== false): ?>
                                            <i class="fas fa-flag-usa"></i>
                                        <?php else: ?>
                                            <i class="fas fa-flag-france"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="test-details">
                                        <h3><?= getTestLabel($rdv['type_test']) ?></h3>
                                        <div class="test-type">
                                            <?= strpos($rdv['type_test'], 'ielts') !== false || strpos($rdv['type_test'], 'toefl') !== false ? 'Test d\'Anglais' : 'Test de Français' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="status-badge" style="background: <?= getStatusColor($rdv['statut']) ?>;">
                                    <?= getStatusIcon($rdv['statut']) ?> <?= getStatusLabel($rdv['statut']) ?>
                                </div>
                            </div>

                            <div class="card-content">
                                <div class="info-grid">
                                    <div class="info-group date-time">
                                        <div class="info-label">
                                            <i class="fas fa-calendar-day"></i> Date et heure
                                        </div>
                                        <div class="info-value">
                                            <?= formatDate($rdv['date_rendezvous']) ?> à <?= formatTime($rdv['heure_rendezvous']) ?>
                                        </div>
                                    </div>

                                    <div class="info-group">
                                        <div class="info-label">
                                            <i class="fas fa-user-circle"></i> Informations personnelles
                                        </div>
                                        <div class="info-value">
                                            <?= htmlspecialchars($rdv['prenom']) ?> <?= htmlspecialchars($rdv['nom']) ?><br>
                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($rdv['email']) ?><br>
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($rdv['telephone']) ?>
                                        </div>
                                    </div>

                                    <div class="info-group">
                                        <div class="info-label">
                                            <i class="fas fa-map-marker-alt"></i> Adresse
                                        </div>
                                        <div class="info-value">
                                            <?= htmlspecialchars($rdv['adresse']) ?><br>
                                            <?= htmlspecialchars($rdv['code_postal']) ?> <?= htmlspecialchars($rdv['ville']) ?><br>
                                            <?= htmlspecialchars(ucfirst($rdv['pays'])) ?>
                                        </div>
                                    </div>

                                    <div class="info-group">
                                        <div class="info-label">
                                            <i class="fas fa-id-card"></i> Pièce d'identité
                                        </div>
                                        <div class="info-value">
                                            <?= getPieceLabel($rdv['type_piece']) ?><br>
                                            <i class="fas fa-hashtag"></i> <?= htmlspecialchars($rdv['numero_piece']) ?><br>
                                            <i class="fas fa-calendar-plus"></i> Émission : <?= formatDate($rdv['date_emission_piece']) ?><br>
                                            <i class="fas fa-calendar-minus"></i> Expiration : <?= formatDate($rdv['date_expiration_piece']) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fichiers uploadés -->
                                <?php if ($rdv['fichier_piece'] || $rdv['fichier_passeport']): ?>
                                    <div class="files-section">
                                        <div class="files-title">
                                            <i class="fas fa-paperclip"></i> Documents uploadés
                                        </div>
                                        <div class="files-list">
                                            <?php if ($rdv['fichier_piece']): ?>
                                                <a href="uploads/<?= htmlspecialchars($rdv['fichier_piece']) ?>" target="_blank" class="file-item">
                                                    <span class="file-icon"><i class="fas fa-file-contract"></i></span>
                                                    <span>Pièce d'identité</span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($rdv['fichier_passeport']): ?>
                                                <a href="uploads/<?= htmlspecialchars($rdv['fichier_passeport']) ?>" target="_blank" class="file-item">
                                                    <span class="file-icon"><i class="fas fa-passport"></i></span>
                                                    <span>Passeport</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="card-actions">
                                    <?php if ($rdv['statut'] === 'en_attente'): ?>
                                        <a href="modifier_rendezvous.php?id=<?= $rdv['id'] ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="rendezvous_id" value="<?= $rdv['id'] ?>">
                                            <input type="hidden" name="annuler_rendezvous" value="1">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="uploads/<?= htmlspecialchars($rdv['fichier_piece']) ?>" target="_blank" class="btn btn-outline">
                                        <i class="fas fa-eye"></i> Voir pièce
                                    </a>
                                    <?php if ($rdv['fichier_passeport']): ?>
                                        <a href="uploads/<?= htmlspecialchars($rdv['fichier_passeport']) ?>" target="_blank" class="btn btn-outline">
                                            <i class="fas fa-passport"></i> Voir passeport
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Animation des cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.rendezvous-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });
        });
    </script>
</body>
</html>