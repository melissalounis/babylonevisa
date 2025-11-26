<?php
// Démarrer la session
session_start();

// Configuration de la base de données
$DB_HOST = 'localhost';
$DB_NAME = 'babylone_service';
$DB_USER = 'root';
$DB_PASS = '';

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connexion BD échouée: " . htmlspecialchars($e->getMessage()));
}

// Fonction d'échappement pour sécurité
function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$client_email = $_SESSION['email'] ?? '';
$page_title = "Mes Messages - Babylone Service";

// Récupérer le nom de l'utilisateur depuis la base de données
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$client_id]);
    $user = $stmt->fetch();
    
    // Déterminer le nom à afficher
    $display_name = 'Utilisateur';
    if ($user) {
        if (!empty($user['prenom']) && !empty($user['nom'])) {
            $display_name = $user['prenom'] . ' ' . $user['nom'];
        } elseif (!empty($user['username'])) {
            $display_name = $user['username'];
        } elseif (!empty($user['prenom'])) {
            $display_name = $user['prenom'];
        } elseif (!empty($user['nom'])) {
            $display_name = $user['nom'];
        } elseif (!empty($user['email'])) {
            $display_name = $user['email'];
        }
        
        // Utiliser l'email de la base de données
        if (!empty($user['email'])) {
            $client_email = $user['email'];
        }
    }
    
} catch (PDOException $e) {
    $display_name = 'Utilisateur';
}

// Récupération des messages - VERSION CORRIGÉE
try {
    // Nettoyer l'email pour la recherche
    $clean_email = trim($client_email);
    
    // DEBUG: Afficher l'email recherché (à commenter après test)
    // echo "<!-- DEBUG: Recherche des messages pour l'email: " . e($clean_email) . " -->";
    
    // Chercher par email exact (avec trim pour éviter les problèmes d'espaces)
    $stmt = $pdo->prepare("
        SELECT * FROM contact_messages 
        WHERE TRIM(email) = TRIM(?) 
        ORDER BY date_envoi DESC
    ");
    $stmt->execute([$clean_email]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Afficher le nombre de messages trouvés (à commenter après test)
    // echo "<!-- DEBUG: " . count($messages) . " messages trouvés avec email exact -->";
    
    // Si aucun message trouvé, chercher par user_id si la colonne existe
    if (empty($messages)) {
        $stmt = $pdo->prepare("
            SELECT * FROM contact_messages 
            WHERE user_id = ? 
            ORDER BY date_envoi DESC
        ");
        $stmt->execute([$client_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // DEBUG: Afficher le nombre de messages trouvés par user_id
        // echo "<!-- DEBUG: " . count($messages) . " messages trouvés avec user_id -->";
    }
    
    // Si toujours aucun message, chercher par email partiel (fallback)
    if (empty($messages)) {
        $stmt = $pdo->prepare("
            SELECT * FROM contact_messages 
            WHERE email LIKE ? 
            ORDER BY date_envoi DESC
        ");
        $stmt->execute(['%' . $clean_email . '%']);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // DEBUG: Afficher le nombre de messages trouvés par email partiel
        // echo "<!-- DEBUG: " . count($messages) . " messages trouvés avec email partiel -->";
    }
    
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages: " . htmlspecialchars($e->getMessage()));
}

// Marquer un message comme lu
if (isset($_POST['mark_as_read']) && isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];
    try {
        $update_stmt = $pdo->prepare("UPDATE contact_messages SET lu = 1, date_lecture = NOW() WHERE id = ?");
        $update_stmt->execute([$message_id]);
        header('Location: messages.php');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors du marquage comme lu: " . htmlspecialchars($e->getMessage()));
    }
}

// Supprimer un message
if (isset($_POST['delete_message']) && isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $delete_stmt->execute([$message_id]);
        header('Location: messages.php');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression: " . htmlspecialchars($e->getMessage()));
    }
}

// Compter les statistiques
$total_messages = count($messages);
$unread_count = 0;
$today_count = 0;

$today = date('Y-m-d');
foreach ($messages as $msg) {
    // Compter les messages non lus
    if (isset($msg['lu']) && $msg['lu'] == 0) {
        $unread_count++;
    }
    
    // Compter les messages d'aujourd'hui
    if (isset($msg['date_envoi']) && date('Y-m-d', strtotime($msg['date_envoi'])) == $today) {
        $today_count++;
    }
}

// Options des sujets
$subject_options = [
    'visa' => 'Demande de visa',
    'etudes' => 'Études à l\'étranger',
    'tourisme' => 'Tourisme',
    'travail' => 'Travail à l\'étranger',
    'autre' => 'Autre demande'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --secondary: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --dark: #343a40;
            --light: #f8f9fa;
            --gray: #6b7280;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ffffff;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 280px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
        }
        
        .stat-card {
            background: white;
            color: var(--dark);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .stat-card.primary::before { background: var(--primary); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.info::before { background: #17a2b8; }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }
        
        .message-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .message-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: var(--primary);
            transition: all 0.3s ease;
        }
        
        .message-card.unread {
            border-left-color: var(--warning);
            background: #fffbf0;
        }
        
        .message-card.unread::before {
            background: var(--warning);
        }
        
        .message-card.read::before {
            background: var(--success);
        }
        
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }
        
        .message-preview {
            color: var(--gray);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .badge-statut {
            font-size: 0.75em;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-item {
            margin-bottom: 8px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 86, 179, 0.3);
            color: white;
        }
        
        .subject-badge {
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 16px 16px 0 0;
            padding: 20px 30px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header p-4 text-center">
                <div class="mb-4">
                    <i class="fas fa-passport fa-3x text-white mb-3"></i>
                    <h3 class="text-white fw-bold">Babylone Service</h3>
                </div>
                <p class="text-white-50 mb-0">Espace Client</p>
                
                <!-- Informations utilisateur -->
                <div class="user-info text-center">
                    <div class="fw-bold text-white fs-5"><?php echo e($display_name); ?></div>
                    <div class="small text-white-80"><?php echo e($client_email); ?></div>
                    <div class="small mt-2">
                        <i class="fas fa-circle text-success me-1"></i>
                        <span class="text-white-80">En ligne</span>
                    </div>
                </div>
            </div>
            
            <ul class="nav flex-column p-3">
                <li class="nav-item">
                    <a href="demandes.php" class="nav-link">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="messages.php" class="nav-link">
                        <i class="fas fa-envelope me-3"></i>
                        Mes Messages
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-warning ms-2"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profil.php" class="nav-link">
                        <i class="fas fa-user me-3"></i>
                        Mon Profil
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt me-3"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-5 fade-in-up">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h2 fw-bold text-dark mb-2">
                                    <i class="fas fa-envelope text-primary me-3"></i>
                                    Mes Messages
                                </h1>
                                <p class="text-muted mb-0">
                                    Bienvenue <span class="text-primary fw-semibold"><?php echo e($display_name); ?></span> • 
                                    Consultez votre historique de messages
                                </p>
                            </div>
                            <a href="../contact.php" class="btn btn-primary-custom">
                                <i class="fas fa-paper-plane me-2"></i> Nouveau Message
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-5 fade-in-up">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-1" style="font-size: 2.5rem;"><?php echo $total_messages; ?></h3>
                                    <p class="mb-0 text-muted">Total Messages</p>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-envelope fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card warning p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-1" style="font-size: 2.5rem;"><?php echo $unread_count; ?></h3>
                                    <p class="mb-0 text-muted">Non lus</p>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-envelope-open fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card success p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-1" style="font-size: 2.5rem;"><?php echo $total_messages - $unread_count; ?></h3>
                                    <p class="mb-0 text-muted">Messages lus</p>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card info p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-1" style="font-size: 2.5rem;"><?php echo $today_count; ?></h3>
                                    <p class="mb-0 text-muted">Aujourd'hui</p>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-calendar-day fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="row fade-in-up">
                    <div class="col-12">
                        <div class="content-card p-4">
                            <?php if (empty($messages)): ?>
                                <div class="empty-state">
                                    <div class="mb-4">
                                        <i class="fas fa-envelope-open-text fa-5x text-muted mb-4"></i>
                                    </div>
                                    <h3 class="text-dark mb-3">Aucun message trouvé</h3>
                                    <p class="text-muted mb-4">
                                        Vous n'avez pas encore envoyé de message via notre formulaire de contact.
                                        <br><small class="text-muted">Email recherché: <?php echo e($client_email); ?></small>
                                    </p>
                                    <a href="../contact.php" class="btn btn-primary-custom btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Envoyer mon premier message
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="mb-0">Vos messages (<?php echo $total_messages; ?>)</h4>
                                    <small class="text-muted">Email: <?php echo e($client_email); ?></small>
                                </div>
                                <div class="messages-list">
                                    <?php foreach ($messages as $message): ?>
                                        <div class="message-card <?php echo ($message['lu'] == 0) ? 'unread' : 'read'; ?> fade-in-up">
                                            <div class="row align-items-center">
                                                <div class="col-lg-8 mb-3 mb-lg-0">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <span class="subject-badge me-3">
                                                            <?php echo e($subject_options[$message['subject']] ?? 'Autre demande'); ?>
                                                        </span>
                                                        <span class="badge-statut <?php echo ($message['lu'] == 1) ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                            <i class="fas <?php echo ($message['lu'] == 1) ? 'fa-check-circle' : 'fa-clock'; ?> me-1"></i>
                                                            <?php echo ($message['lu'] == 1) ? 'Lu' : 'Non lu'; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="message-preview mb-3">
                                                        <?php 
                                                        $preview = $message['message'] ?? '';
                                                        if (strlen($preview) > 200) {
                                                            echo nl2br(e(substr($preview, 0, 200) . '...'));
                                                        } else {
                                                            echo nl2br(e($preview));
                                                        }
                                                        ?>
                                                    </div>
                                                    
                                                    <div class="text-muted small">
                                                        <i class="fas fa-calendar me-2"></i>
                                                        <?php 
                                                        if (isset($message['date_envoi']) && !empty($message['date_envoi'])) {
                                                            echo date('d/m/Y à H:i', strtotime($message['date_envoi']));
                                                        } else {
                                                            echo 'Date non disponible';
                                                        }
                                                        ?>
                                                        <span class="mx-2">•</span>
                                                        <i class="fas fa-user me-2"></i><?php echo e($message['name']); ?>
                                                        <span class="mx-2">•</span>
                                                        <i class="fas fa-envelope me-2"></i><?php echo e($message['email']); ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-4 text-lg-end">
                                                    <div class="btn-group">
                                                        <?php if ($message['lu'] == 0): ?>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="message_id" value="<?php echo e($message['id']); ?>">
                                                                <button type="submit" name="mark_as_read" class="btn btn-outline-success btn-sm" title="Marquer comme lu">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-outline-primary btn-sm view-message" 
                                                                data-message-id="<?php echo e($message['id']); ?>"
                                                                data-message-subject="<?php echo e($subject_options[$message['subject']] ?? 'Autre demande'); ?>"
                                                                data-message-date="<?php echo date('d/m/Y à H:i', strtotime($message['date_envoi'])); ?>"
                                                                data-message-name="<?php echo e($message['name']); ?>"
                                                                data-message-email="<?php echo e($message['email']); ?>"
                                                                data-message-phone="<?php echo e($message['phone'] ?? 'Non renseigné'); ?>"
                                                                data-message-content="<?php echo e($message['message']); ?>"
                                                                data-message-status="<?php echo ($message['lu'] == 1) ? 'Lu' : 'Non lu'; ?>"
                                                                title="Voir le message">
                                                            <i class="fas fa-expand"></i>
                                                        </button>
                                                        
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                                            <input type="hidden" name="message_id" value="<?php echo e($message['id']); ?>">
                                                            <button type="submit" name="delete_message" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Principal pour afficher les détails du message -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-envelope me-2"></i>
                        Détails du message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Le contenu sera chargé par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ouvrir le modal avec toutes les informations
            document.querySelectorAll('.view-message').forEach(button => {
                button.addEventListener('click', function() {
                    const messageId = this.getAttribute('data-message-id');
                    const subject = this.getAttribute('data-message-subject');
                    const date = this.getAttribute('data-message-date');
                    const name = this.getAttribute('data-message-name');
                    const email = this.getAttribute('data-message-email');
                    const phone = this.getAttribute('data-message-phone');
                    const content = this.getAttribute('data-message-content');
                    const status = this.getAttribute('data-message-status');
                    
                    // Construire le contenu du modal
                    const modalContent = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6 class="fw-bold text-primary mb-3">Informations du message</h6>
                                    <p><strong>Sujet:</strong><br>${subject}</p>
                                    <p><strong>Date d'envoi:</strong><br>${date}</p>
                                    <p><strong>Statut:</strong><br>
                                        <span class="badge ${status === 'Lu' ? 'bg-success' : 'bg-warning text-dark'}">
                                            ${status}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6 class="fw-bold text-primary mb-3">Vos coordonnées</h6>
                                    <p><strong>Nom:</strong><br>${name}</p>
                                    <p><strong>Email:</strong><br>${email}</p>
                                    <p><strong>Téléphone:</strong><br>${phone}</p>
                                    <p><strong>ID Message:</strong><br>#${messageId}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h6 class="fw-bold text-primary mb-3">Contenu du message</h6>
                            <div class="message-content p-3 bg-light rounded" style="white-space: pre-line; line-height: 1.6;">
                                ${content}
                            </div>
                        </div>
                    `;
                    
                    // Mettre à jour le modal
                    document.getElementById('modalTitle').innerHTML = `<i class="fas fa-envelope me-2"></i>${subject}`;
                    document.getElementById('modalBody').innerHTML = modalContent;
                    
                    // Afficher le modal
                    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                    modal.show();
                });
            });

            // Animation au scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observer les éléments à animer
            document.querySelectorAll('.fade-in-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>