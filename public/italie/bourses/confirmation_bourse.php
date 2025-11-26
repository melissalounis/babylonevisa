<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer l'ID de la demande depuis l'URL
$demande_id = $_GET['id'] ?? 0;

if (!$demande_id) {
    header("Location: bourses.php");
    exit;
}

// Récupérer les informations de la demande
$stmt = $pdo->prepare("
    SELECT db.*, u.email as user_email 
    FROM demandes_bourse_italie db 
    LEFT JOIN users u ON db.user_id = u.id 
    WHERE db.id = ? AND db.user_id = ?
");
$stmt->execute([$demande_id, $_SESSION['user_id']]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    header("Location: bourses.php");
    exit;
}

// Récupérer les fichiers associés
$stmt_fichiers = $pdo->prepare("SELECT * FROM demandes_bourse_fichiers WHERE demande_id = ?");
$stmt_fichiers->execute([$demande_id]);
$fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);

// Formater les données pour l'affichage
$types_bourse = [
    'excellence' => 'Bourse d\'Excellence',
    'merite' => 'Bourse au Mérite',
    'sportive' => 'Bourse Sportive',
    'culturelle' => 'Bourse Culturelle/Artistique',
    'recherche' => 'Bourse de Recherche'
];

$niveaux_etudes = [
    'licence1' => 'Licence 1',
    'licence2' => 'Licence 2',
    'licence3' => 'Licence 3',
    'master1' => 'Master 1',
    'master2' => 'Master 2',
    'doctorat' => 'Doctorat'
];

$statuts = [
    'en_attente' => 'En attente',
    'en_cours' => 'En cours de traitement',
    'approuvee' => 'Approuvée',
    'refusee' => 'Refusée'
];

$tests_langues = [
    'non' => 'Non',
    'celi' => 'CELI',
    'cils' => 'CILS',
    'plida' => 'PLIDA',
    'ielts' => 'IELTS',
    'toefl' => 'TOEFL',
    'cambridge' => 'Cambridge',
    'autre' => 'Autre'
];

$page_title = "Confirmation de Demande de Bourse";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #008C45;
            --secondary-color: #CD212A;
            --accent-color: #F4F5F0;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #008C45, #CD212A);
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .confirmation-container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .confirmation-header {
            background: linear-gradient(135deg, #008C45, #CD212A);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .confirmation-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            animation: float 20s linear infinite;
        }

        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        .confirmation-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .confirmation-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .confirmation-content {
            padding: 40px 30px;
        }

        .info-card {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }

        .info-card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h3 i {
            color: var(--secondary-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
            display: block;
        }

        .info-value {
            color: #666;
            background: white;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }

        .fichiers-list {
            list-style: none;
            padding: 0;
        }

        .fichier-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .fichier-item:hover {
            border-color: var(--primary-color);
            transform: translateX(5px);
        }

        .fichier-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fichier-icon {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .fichier-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #008C45, #CD212A);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .demande-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 15px;
            display: inline-block;
        }

        .next-steps {
            background: #e7f3ff;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-top: 30px;
            border-left: 4px solid var(--info-color);
        }

        .next-steps h4 {
            color: var(--info-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .next-steps ul {
            list-style: none;
            padding: 0;
        }

        .next-steps li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
        }

        .next-steps li:before {
            content: "✓";
            color: var(--success-color);
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes float {
            from {
                transform: translateX(-100px);
            }
            to {
                transform: translateX(100px);
            }
        }

        @media (max-width: 768px) {
            .confirmation-header {
                padding: 30px 20px;
            }

            .confirmation-header h1 {
                font-size: 2rem;
            }

            .confirmation-content {
                padding: 30px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Demande Enregistrée !</h1>
            <p>Votre demande de bourse a été soumise avec succès</p>
            <div class="demande-number">
                Référence : #<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <div class="confirmation-content">
            <!-- Informations de la demande -->
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Détails de la Demande</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Type de bourse</span>
                        <div class="info-value"><?php echo htmlspecialchars($types_bourse[$demande['type_bourse']] ?? $demande['type_bourse']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Niveau d'études</span>
                        <div class="info-value"><?php echo htmlspecialchars($niveaux_etudes[$demande['niveau_etudes']] ?? $demande['niveau_etudes']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Domaine d'études</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Université</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['universite_choisie']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Programme</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['programme']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durée</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['duree_etudes']); ?> année(s)</div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Moyenne</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['moyenne']); ?>/20</div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Statut</span>
                        <div class="info-value" style="color: 
                            <?php echo $demande['statut'] == 'approuvee' ? 'var(--success-color)' : 
                                  ($demande['statut'] == 'refusee' ? 'var(--secondary-color)' : 
                                  ($demande['statut'] == 'en_cours' ? 'var(--warning-color)' : 'var(--info-color)')); ?>; 
                            font-weight: 600;">
                            <?php echo htmlspecialchars($statuts[$demande['statut']] ?? $demande['statut']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="info-card">
                <h3><i class="fas fa-user-graduate"></i> Informations Personnelles</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nom complet</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date de naissance</span>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Lieu de naissance</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['lieu_naissance']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nationalité</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['nationalite']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <div class="info-value"><?php echo htmlspecialchars($demande['telephone']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Tests de langues -->
            <div class="info-card">
                <h3><i class="fas fa-language"></i> Tests de Langues</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Test d'italien</span>
                        <div class="info-value"><?php echo htmlspecialchars($tests_langues[$demande['tests_italien']] ?? $demande['tests_italien']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Test d'anglais</span>
                        <div class="info-value"><?php echo htmlspecialchars($tests_langues[$demande['tests_anglais']] ?? $demande['tests_anglais']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Fichiers uploadés -->
            <div class="info-card">
                <h3><i class="fas fa-file-upload"></i> Documents Transmis</h3>
                <ul class="fichiers-list">
                    <?php foreach ($fichiers as $fichier): ?>
                        <li class="fichier-item">
                            <div class="fichier-info">
                                <i class="fas fa-file-pdf fichier-icon"></i>
                                <span><?php echo htmlspecialchars($fichier['type_fichier']); ?></span>
                            </div>
                            <span class="fichier-status status-success">Uploadé</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Prochaines étapes -->
            <div class="next-steps">
                <h4><i class="fas fa-list-alt"></i> Prochaines Étapes</h4>
                <ul>
                    <li>Votre demande est en cours de traitement</li>
                    <li>Vous recevrez un email de confirmation sous 24h</li>
                    <li>Notre équipe analysera votre dossier sous 3-5 jours ouvrables</li>
                    <li>Vous serez informé par email de la décision</li>
                    <li>Consultez votre espace personnel pour suivre l'avancement</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="actions">
                <a href="bourses.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle Demande
                </a>
                <a href="../mes_demandes.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Mes Demandes
                </a>
                <a href="../../index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </div>
        </div>
    </div>

    <script>
        // Animation d'apparition progressive
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.info-card, .next-steps');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // Impression de la confirmation
        function imprimerConfirmation() {
            window.print();
        }

        // Ajout du bouton d'impression
        document.addEventListener('DOMContentLoaded', function() {
            const actions = document.querySelector('.actions');
            const printBtn = document.createElement('button');
            printBtn.className = 'btn btn-outline';
            printBtn.innerHTML = '<i class="fas fa-print"></i> Imprimer';
            printBtn.onclick = imprimerConfirmation;
            actions.appendChild(printBtn);
        });
    </script>
</body>
</html>