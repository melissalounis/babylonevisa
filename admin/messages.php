<?php
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_secure', 1);

// Puis démarrer la session
session_start();
// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Erreur de connexion à la base de données");
}

// Gestion des actions (marquer comme lu/supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $message_id = (int)$_POST['id'];
        
        try {
            if ($_POST['action'] === 'marquer_lu') {
                $stmt = $pdo->prepare("UPDATE contact_messages SET lu = 1, date_lecture = NOW() WHERE id = ?");
                $stmt->execute([$message_id]);
                $_SESSION['flash_message'] = "Message marqué comme lu";
                
            } elseif ($_POST['action'] === 'marquer_non_lu') {
                $stmt = $pdo->prepare("UPDATE contact_messages SET lu = 0, date_lecture = NULL WHERE id = ?");
                $stmt->execute([$message_id]);
                $_SESSION['flash_message'] = "Message marqué comme non lu";
                
            } elseif ($_POST['action'] === 'supprimer') {
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$message_id]);
                $_SESSION['flash_message'] = "Message supprimé avec succès";
            }
            
        } catch(PDOException $e) {
            error_log("Erreur action message: " . $e->getMessage());
            $_SESSION['flash_error'] = "Erreur lors de l'opération";
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Récupérer tous les messages
try {
    $stmt = $pdo->query("
        SELECT id, name, email, phone, subject, message, date_envoi, lu, date_lecture 
        FROM contact_messages 
        ORDER BY lu ASC, date_envoi DESC
    ");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erreur récupération messages: " . $e->getMessage());
    $messages = [];
    $error_message = "Erreur lors de la récupération des messages";
}

// Compter les messages non lus
$messages_non_lus = array_filter($messages, function($msg) {
    return !$msg['lu'];
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Messages - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .message-non-lu {
            background-color: #e8f4fd !important;
            font-weight: 600;
        }
        
        .message-preview {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .badge-statut {
            font-size: 0.8em;
            padding: 6px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header p-3">
                <h4 class="text-center">
                    <i class="fas fa-passport"></i><br>
                    Babylone Service
                </h4>
                <p class="text-center mb-0">Espace Administrateur</p>
            </div>
            
            <ul class="nav flex-column p-3">
                <li class="nav-item mb-2">
                    <a href="admin_dashboard.php" class="nav-link text-white">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_rendez_vous.php" class="nav-link text-white">
                        <i class="fas fa-list me-2"></i>
                        Rendez-vous
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_messages.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-envelope me-2"></i>
                        Messages
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_clients.php" class="nav-link text-white">
                        <i class="fas fa-users me-2"></i>
                        Clients
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a href="logout.php" class="nav-link text-white">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        Gestion des Messages
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez tous les messages de contact</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exporterMessages()">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count($messages); ?></h4>
                                    <p class="mb-0">Total Messages</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-envelope fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count($messages_non_lus); ?></h4>
                                    <p class="mb-0">Non lus</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-envelope-open fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        <?php 
                                        $messages_lus = array_filter($messages, function($msg) {
                                            return $msg['lu'];
                                        });
                                        echo count($messages_lus);
                                        ?>
                                    </h4>
                                    <p class="mb-0">Messages lus</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        <?php 
                                        $aujourd_hui = array_filter($messages, function($msg) {
                                            return date('Y-m-d') === date('Y-m-d', strtotime($msg['date_envoi']));
                                        });
                                        echo count($aujourd_hui);
                                        ?>
                                    </h4>
                                    <p class="mb-0">Aujourd'hui</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Flash -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Tableau des messages -->
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Expéditeur</th>
                            <th>Contact</th>
                            <th>Sujet</th>
                            <th>Message</th>
                            <th>Date d'envoi</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun message trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($messages as $msg): ?>
                                <tr class="<?php echo !$msg['lu'] ? 'message-non-lu' : ''; ?>">
                                    <td>
                                        <strong class="text-primary">#<?php echo htmlspecialchars($msg['id']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($msg['name']); ?></div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($msg['email']); ?>
                                        </div>
                                        <?php if (!empty($msg['phone'])): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($msg['phone']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($msg['subject']); ?>
                                    </td>
                                    <td>
                                        <div class="message-preview" data-bs-toggle="tooltip" 
                                             title="<?php echo htmlspecialchars($msg['message']); ?>">
                                            <?php echo nl2br(htmlspecialchars(mb_substr($msg['message'], 0, 80))); ?>...
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($msg['date_envoi'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($msg['lu']): ?>
                                            <span class="badge badge-statut bg-success">
                                                <i class="fas fa-check me-1"></i>Lu
                                                <?php if (!empty($msg['date_lecture'])): ?>
                                                <br>
                                                <small><?php echo date('d/m H:i', strtotime($msg['date_lecture'])); ?></small>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-statut bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>Non lu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir le message"
                                                    onclick="afficherMessage(<?php echo htmlspecialchars(json_encode($msg)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (!$msg['lu']): ?>
                                                <button class="btn btn-outline-success" title="Marquer comme lu"
                                                        onclick="changerStatut(<?php echo $msg['id']; ?>, 'marquer_lu')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-warning" title="Marquer comme non lu"
                                                        onclick="changerStatut(<?php echo $msg['id']; ?>, 'marquer_non_lu')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger" title="Supprimer"
                                                    onclick="supprimerMessage(<?php echo $msg['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher le message complet -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Message de contact</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="messageContent">
                    <!-- Le contenu du message sera chargé ici par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="repondreMessage()">
                        <i class="fas fa-reply me-1"></i> Répondre
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function afficherMessage(message) {
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            const content = document.getElementById('messageContent');
            
            content.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Informations de l'expéditeur</h6>
                        <p><strong>Nom:</strong> ${message.name}</p>
                        <p><strong>Email:</strong> ${message.email}</p>
                        ${message.phone ? `<p><strong>Téléphone:</strong> ${message.phone}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        <h6>Informations du message</h6>
                        <p><strong>Sujet:</strong> ${message.subject}</p>
                        <p><strong>Date d'envoi:</strong> ${new Date(message.date_envoi).toLocaleString('fr-FR')}</p>
                        <p><strong>Statut:</strong> ${message.lu ? '<span class="badge bg-success">Lu</span>' : '<span class="badge bg-warning">Non lu</span>'}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Message:</h6>
                        <div class="border p-3 bg-light rounded">
                            ${message.message.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
            `;
            
            modal.show();
        }
        
        function changerStatut(id, action) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Erreur lors du changement de statut');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors du changement de statut');
            });
        }
        
        function supprimerMessage(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.')) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', 'supprimer');
                
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de la suppression');
                });
            }
        }
        
        function repondreMessage() {
            const email = document.querySelector('#messageContent p strong:contains("Email")')?.parentNode?.textContent.split(':')[1]?.trim();
            if (email) {
                window.location.href = `mailto:${email}`;
            }
        }
        
        function exporterMessages() {
            // Fonctionnalité d'export à implémenter
            alert('Fonctionnalité d\'export à venir');
        }
        
        // Initialisation des tooltips Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>