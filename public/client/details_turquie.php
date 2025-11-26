<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de la demande est spécifié
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_turquie.php");
    exit;
}

$demande_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de la demande SANS jointure
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_turquie 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si la demande existe et appartient à l'utilisateur
    if (!$demande) {
        header("Location: mes_demandes_turquie.php");
        exit;
    }

    // Récupérer l'historique des statuts (si la table existe)
    $historique = [];
    try {
        $stmt_historique = $pdo->prepare("
            SELECT * FROM historique_statuts 
            WHERE demande_id = ? AND type_demande = 'turquie'
            ORDER BY date_changement DESC
        ");
        $stmt_historique->execute([$demande_id]);
        $historique = $stmt_historique->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Si la table n'existe pas, on continue sans historique
        $historique = [];
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le statut
function formatStatutTurquie($statut) {
    $statuts = [
        'en_attente' => ['label' => '⏳ En attente', 'class' => 'en_attente', 'icon' => 'clock'],
        'en_traitement' => ['label' => '🔧 En traitement', 'class' => 'en_traitement', 'icon' => 'cogs'],
        'approuvee' => ['label' => '✅ Approuvée', 'class' => 'approuvee', 'icon' => 'check-circle'],
        'refusee' => ['label' => '❌ Refusée', 'class' => 'refusee', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'en_attente', 'icon' => 'question-circle'];
}

// Fonction pour formater le niveau d'études
function formatNiveauTurquie($niveau) {
    $niveaux = [
        'bac' => 'Baccalauréat',
        'l1' => 'Licence 1',
        'l2' => 'Licence 2', 
        'l3' => 'Licence 3',
        'master' => 'Master',
        'doctorat' => 'Doctorat'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la langue
function formatLangueTurquie($langue) {
    $langues = [
        'turc' => 'Turc',
        'anglais' => 'Anglais',
        'bilingue' => 'Bilingue Turc/Anglais'
    ];
    return $langues[$langue] ?? $langue;
}

// Fonction pour formater le type de certificat
function formatCertificatType($type) {
    $types = [
        'tys' => 'TYS (Test de Turc)',
        'toefl' => 'TOEFL',
        'ielts' => 'IELTS',
        'sans' => 'Aucun certificat'
    ];
    return $types[$type] ?? strtoupper($type);
}

// Déterminer la date de création
function getDateCreation($demande) {
    if (isset($demande['created_at']) && !empty($demande['created_at'])) {
        return $demande['created_at'];
    } elseif (isset($demande['date_creation']) && !empty($demande['date_creation'])) {
        return $demande['date_creation'];
    } else {
        return 'Non spécifiée';
    }
}

// Fonction pour obtenir le nom complet
function getNomComplet($demande) {
    // Essayer d'abord les champs de la table demandes_turquie
    if (!empty($demande['prenom']) && !empty($demande['nom'])) {
        return $demande['prenom'] . ' ' . $demande['nom'];
    }
    // Sinon utiliser l'email comme identifiant
    elseif (!empty($demande['email'])) {
        return 'Utilisateur: ' . $demande['email'];
    }
    else {
        return 'Non spécifié';
    }
}

// Fonction pour obtenir l'email
function getEmail($demande) {
    if (!empty($demande['email'])) {
        return $demande['email'];
    } else {
        return $_SESSION['user_email'] ?? 'Non spécifié';
    }
}

$statut = formatStatutTurquie($demande['statut']);
$dateCreation = getDateCreation($demande);
$nomComplet = getNomComplet($demande);
$email = getEmail($demande);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Demande Turquie - <?php echo 'TUR-' . str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #E30A17;
            --primary-hover: #c90814;
            --secondary-color: #FFFFFF;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
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
            background: var(--light-gray);
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), #c90814);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, white 50%, var(--primary-color) 100%);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-info h1 {
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .reference {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            backdrop-filter: blur(10px);
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-en_traitement {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-approuvee {
            background: #d4edda;
            color: #155724;
        }

        .status-refusee {
            background: #f8d7da;
            color: #721c24;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .card-header {
            background: var(--light-gray);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 {
            color: var(--primary-color);
            margin: 0;
        }

        .card-body {
            padding: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-group {
            margin-bottom: 25px;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-primary {
            background: var(--primary-color);
            color: white;
        }

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-info {
            background: var(--info-color);
            color: white;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 25px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .timeline-item.current::before {
            background: var(--success-color);
            box-shadow: 0 0 0 2px var(--success-color);
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        .timeline-content {
            background: var(--light-gray);
            padding: 15px;
            border-radius: var(--border-radius);
            border-left: 3px solid var(--primary-color);
        }

        .timeline-content.current {
            border-left-color: var(--success-color);
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--primary-hover));
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 8px;
        }

        .documents-list {
            list-style: none;
        }

        .documents-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .documents-list li:last-child {
            border-bottom: none;
        }

        .document-status {
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .status-validé {
            background: #d4edda;
            color: #155724;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-manquant {
            background: #f8d7da;
            color: #721c24;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #d1ecf1;
            border-left-color: var(--info-color);
            color: #0c5460;
        }

        .alert-warning {
            background: #fff3cd;
            border-left-color: var(--warning-color);
            color: #856404;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }

        footer {
            text-align: center;
            padding: 30px;
            color: #666;
            margin-top: 40px;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="header-content">
                <div class="header-info">
                    <h1><i class="fas fa-graduation-cap"></i> Détails de la Demande</h1>
                    <div class="reference">TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="status-badge status-<?php echo $statut['class']; ?>">
                    <i class="fas fa-<?php echo $statut['icon']; ?>"></i>
                    <?php echo $statut['label']; ?>
                </div>
            </div>
        </div>

        <!-- Grille de contenu -->
        <div class="content-grid">
            <!-- Colonne principale -->
            <div>
                <!-- Informations de la demande -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Informations de la Demande</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-label">Spécialité demandée</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['specialite']); ?></div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Niveau d'études</div>
                                <div class="info-value">
                                    <span class="badge badge-primary">
                                        <?php echo formatNiveauTurquie($demande['niveau']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Langue du programme</div>
                                <div class="info-value">
                                    <span class="badge badge-info">
                                        <?php echo formatLangueTurquie($demande['programme_langue']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Test de langue</div>
                                <div class="info-value">
                                    <?php if (!empty($demande['certificat_type']) && $demande['certificat_type'] !== 'sans'): ?>
                                        <strong><?php echo formatCertificatType($demande['certificat_type']); ?></strong>
                                        <?php if (!empty($demande['certificat_score'])): ?>
                                            <br>Score : <?php echo htmlspecialchars($demande['certificat_score']); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #666;">Aucun test requis</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Date de soumission</div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y à H:i', strtotime($dateCreation)); ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Dernière mise à jour</div>
                                <div class="info-value">
                                    <?php 
                                        $dateUpdate = isset($demande['updated_at']) && !empty($demande['updated_at']) ? 
                                            $demande['updated_at'] : $dateCreation;
                                        echo date('d/m/Y à H:i', strtotime($dateUpdate)); 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Notes supplémentaires -->
                        <?php if (!empty($demande['notes']) || !empty($demande['commentaires'])): ?>
                        <div class="info-group">
                            <div class="info-label">Notes supplémentaires</div>
                            <div class="info-value">
                                <?php 
                                    $notes = !empty($demande['notes']) ? $demande['notes'] : ($demande['commentaires'] ?? '');
                                    echo nl2br(htmlspecialchars($notes));
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informations personnelles -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h2>Informations Personnelles</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-group">
                                <div class="info-label">Nom complet</div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($nomComplet); ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value">
                                    <?php 
                                        if (!empty($demande['telephone'])) {
                                            echo htmlspecialchars($demande['telephone']);
                                        } elseif (!empty($demande['phone'])) {
                                            echo htmlspecialchars($demande['phone']);
                                        } else {
                                            echo 'Non spécifié';
                                        }
                                    ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Date de naissance</div>
                                <div class="info-value">
                                    <?php 
                                        if (!empty($demande['date_naissance'])) {
                                            echo date('d/m/Y', strtotime($demande['date_naissance']));
                                        } else {
                                            echo 'Non spécifiée';
                                        }
                                    ?>
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="info-label">Nationalité</div>
                                <div class="info-value">
                                    <?php 
                                        if (!empty($demande['nationalite'])) {
                                            echo htmlspecialchars($demande['nationalite']);
                                        } else {
                                            echo 'Non spécifiée';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div>
                <!-- Historique des statuts -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i>
                        <h2>Suivi de la Demande</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($historique)): ?>
                            <div class="timeline">
                                <?php foreach ($historique as $index => $event): ?>
                                    <div class="timeline-item <?php echo $index === 0 ? 'current' : ''; ?>">
                                        <div class="timeline-date">
                                            <?php echo date('d/m/Y à H:i', strtotime($event['date_changement'])); ?>
                                        </div>
                                        <div class="timeline-content <?php echo $index === 0 ? 'current' : ''; ?>">
                                            <strong><?php echo formatStatutTurquie($event['ancien_statut'])['label']; ?></strong>
                                            →
                                            <strong><?php echo formatStatutTurquie($event['nouveau_statut'])['label']; ?></strong>
                                            <?php if (!empty($event['commentaire'])): ?>
                                                <br><small><?php echo htmlspecialchars($event['commentaire']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <p>Aucun historique disponible</p>
                                <small>L'historique des statuts apparaîtra ici</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Documents requis -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-file-alt"></i>
                        <h2>Documents</h2>
                    </div>
                    <div class="card-body">
                        <ul class="documents-list">
                            <li>
                                <span>Passeport</span>
                                <span class="document-status status-<?php echo !empty($demande['passeport']) ? 'validé' : 'manquant'; ?>">
                                    <?php echo !empty($demande['passeport']) ? '✓ Fourni' : '✗ Manquant'; ?>
                                </span>
                            </li>
                            <li>
                                <span>Diplômes</span>
                                <span class="document-status status-<?php echo !empty($demande['diplomes']) ? 'validé' : 'manquant'; ?>">
                                    <?php echo !empty($demande['diplomes']) ? '✓ Fourni' : '✗ Manquant'; ?>
                                </span>
                            </li>
                            <li>
                                <span>Relevés de notes</span>
                                <span class="document-status status-<?php echo !empty($demande['releves_notes']) ? 'validé' : 'manquant'; ?>">
                                    <?php echo !empty($demande['releves_notes']) ? '✓ Fourni' : '✗ Manquant'; ?>
                                </span>
                            </li>
                            <li>
                                <span>Photo d'identité</span>
                                <span class="document-status status-<?php echo !empty($demande['photo_identite']) ? 'validé' : 'manquant'; ?>">
                                    <?php echo !empty($demande['photo_identite']) ? '✓ Fourni' : '✗ Manquant'; ?>
                                </span>
                            </li>
                            <?php if (!empty($demande['certificat_type']) && $demande['certificat_type'] !== 'sans'): ?>
                            <li>
                                <span>Certificat de langue</span>
                                <span class="document-status status-<?php echo !empty($demande['certificat_langue']) ? 'validé' : 'manquant'; ?>">
                                    <?php echo !empty($demande['certificat_langue']) ? '✓ Fourni' : '✗ Manquant'; ?>
                                </span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <i class="fas fa-cogs"></i>
                        <h2>Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="actions">
                            <a href="mes_demandes_turquie.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                            <?php if ($demande['statut'] === 'en_attente'): ?>
                                <a href="modifier_turquie.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations importantes -->
        <div class="alert alert-info">
            <h3><i class="fas fa-info-circle"></i> Informations importantes</h3>
            <p>Pour toute question concernant votre demande, contactez-nous à :</p>
            <p><strong>Email :</strong> babylone.service15@gmail.com | <strong>Téléphone :</strong> +213 554 31 00 47</p>
            <p>Les délais de traitement peuvent varier de 2 à 4 semaines selon la période et la complexité de votre dossier.</p>
        </div>

        <footer>
            <p>© <?php echo date('Y'); ?> Études Turquie - Babylone Service</p>
            <p>Dernière consultation : <?php echo date('d/m/Y à H:i'); ?></p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page de détails chargée - Demande TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>');
        });
    </script>
</body>
</html>