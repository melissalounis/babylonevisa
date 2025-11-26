<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les demandes de CV de l'utilisateur
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_creation_cv 
        WHERE user_id = ? 
        ORDER BY date_creation DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques
    $total_demandes = count($demandes);
    $demandes_traitement = 0;
    $demandes_terminees = 0;
    $demandes_annulees = 0;

    foreach ($demandes as $demande) {
        switch ($demande['statut']) {
            case 'en_traitement':
                $demandes_traitement++;
                break;
            case 'termine':
                $demandes_terminees++;
                break;
            case 'annule':
                $demandes_annulees++;
                break;
        }
    }

} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
    $demandes = [];
    $total_demandes = 0;
    $demandes_traitement = 0;
    $demandes_terminees = 0;
    $demandes_annulees = 0;
}

// Fonction pour formater la date
function formaterDate($date) {
    return $date ? date('d/m/Y à H:i', strtotime($date)) : '-';
}

// Fonction pour obtenir la classe CSS du statut
function getStatutClass($statut) {
    switch ($statut) {
        case 'en_traitement':
            return 'statut-traitement';
        case 'termine':
            return 'statut-termine';
        case 'annule':
            return 'statut-annule';
        default:
            return 'statut-traitement';
    }
}

// Fonction pour obtenir le libellé du statut
function getStatutLabel($statut) {
    switch ($statut) {
        case 'en_traitement':
            return 'En traitement';
        case 'termine':
            return 'Terminé';
        case 'annule':
            return 'Annulé';
        default:
            return $statut;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de CV - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card.total i { color: var(--primary); }
        .stat-card.traitement i { color: var(--warning); }
        .stat-card.termine i { color: var(--success); }
        .stat-card.annule i { color: var(--danger); }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .demandes-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
        }

        .card-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .demande-item {
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .demande-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e1e8ed;
        }

        .demande-numero {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary);
        }

        .demande-statut {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .statut-traitement {
            background: #fff3cd;
            color: #856404;
        }

        .statut-termine {
            background: #d4edda;
            color: #155724;
        }

        .statut-annule {
            background: #f8d7da;
            color: #721c24;
        }

        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .demande-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .content-section {
            margin-bottom: 15px;
        }

        .content-section:last-child {
            margin-bottom: 0;
        }

        .content-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .content-text {
            color: #555;
            line-height: 1.5;
        }

        .formation-item, .experience-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 3px solid var(--secondary);
        }

        .formation-item:last-child, .experience-item:last-child {
            margin-bottom: 0;
        }

        .item-title {
            font-weight: 600;
            color: var(--dark);
        }

        .item-details {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin: 5px 0;
        }

        .demande-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-danger {
            background: transparent;
            border: 2px solid var(--danger);
            color: var(--danger);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .commentaires {
            background: #e8f4fd;
            border: 1px solid #b6d7e8;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .commentaires-title {
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .demande-details {
                grid-template-columns: 1fr;
            }

            .demande-actions {
                flex-direction: column;
            }

            .navigation {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Mes Demandes de CV</h1>
            <p>Suivez l'état d'avancement de vos demandes de création de CV</p>
        </div>

        <div class="navigation">
            <a href="creation_cv.php" class="btn">
                <i class="fas fa-plus"></i> Nouvelle Demande de CV
            </a>
            <div>
                <a href="espace_client.php" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Mon Espace
                </a>
            </div>
        </div>

        <div class="stats-cards">
            <div class="stat-card total">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?php echo $total_demandes; ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
            <div class="stat-card traitement">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?php echo $demandes_traitement; ?></div>
                <div class="stat-label">En traitement</div>
            </div>
            <div class="stat-card termine">
                <i class="fas fa-check-circle"></i>
                <div class="stat-number"><?php echo $demandes_terminees; ?></div>
                <div class="stat-label">Terminées</div>
            </div>
            <div class="stat-card annule">
                <i class="fas fa-times-circle"></i>
                <div class="stat-number"><?php echo $demandes_annulees; ?></div>
                <div class="stat-label">Annulées</div>
            </div>
        </div>

        <div class="demandes-list">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Historique des Demandes</h2>
            </div>
            <div class="card-body">
                <?php if (empty($demandes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune demande de CV</h3>
                        <p>Vous n'avez pas encore soumis de demande de création de CV.</p>
                        <a href="creation_cv.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Créer une nouvelle demande
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($demandes as $demande): ?>
                        <div class="demande-item">
                            <div class="demande-header">
                                <div class="demande-numero">
                                    <i class="fas fa-file-invoice"></i> Demande #<?php echo $demande['id']; ?>
                                </div>
                                <div class="demande-statut <?php echo getStatutClass($demande['statut']); ?>">
                                    <?php echo getStatutLabel($demande['statut']); ?>
                                </div>
                            </div>

                            <div class="demande-details">
                                <div class="detail-item">
                                    <span class="detail-label">Nom complet</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($demande['email']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Téléphone</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($demande['telephone']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date de création</span>
                                    <span class="detail-value"><?php echo formaterDate($demande['date_creation']); ?></span>
                                </div>
                            </div>

                            <div class="demande-content">
                                <!-- Formations -->
                                <?php if (!empty($demande['formations'])): 
                                    $formations = json_decode($demande['formations'], true); ?>
                                    <div class="content-section">
                                        <div class="content-title">
                                            <i class="fas fa-graduation-cap"></i> Formations
                                        </div>
                                        <?php foreach ($formations as $formation): ?>
                                            <div class="formation-item">
                                                <div class="item-title"><?php echo htmlspecialchars($formation['diplome']); ?></div>
                                                <div class="item-details">
                                                    <?php echo htmlspecialchars($formation['etablissement']); ?>
                                                    <?php if (!empty($formation['annee_obtention'])): ?>
                                                        - <?php echo htmlspecialchars($formation['annee_obtention']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($formation['description'])): ?>
                                                    <div class="content-text"><?php echo nl2br(htmlspecialchars($formation['description'])); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Expériences professionnelles -->
                                <?php if (!empty($demande['experiences'])): 
                                    $experiences = json_decode($demande['experiences'], true); ?>
                                    <div class="content-section">
                                        <div class="content-title">
                                            <i class="fas fa-briefcase"></i> Expériences Professionnelles
                                        </div>
                                        <?php foreach ($experiences as $experience): ?>
                                            <div class="experience-item">
                                                <div class="item-title"><?php echo htmlspecialchars($experience['poste']); ?></div>
                                                <div class="item-details">
                                                    <?php echo htmlspecialchars($experience['entreprise']); ?>
                                                    <?php if (!empty($experience['date_debut'])): ?>
                                                        - <?php echo formaterDate($experience['date_debut']); ?>
                                                        <?php if (!empty($experience['date_fin'])): ?>
                                                            à <?php echo formaterDate($experience['date_fin']); ?>
                                                        <?php else: ?>
                                                            (En cours)
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($experience['description'])): ?>
                                                    <div class="content-text"><?php echo nl2br(htmlspecialchars($experience['description'])); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Compétences -->
                                <div class="content-section">
                                    <div class="content-title">
                                        <i class="fas fa-star"></i> Compétences
                                    </div>
                                    <?php if (!empty($demande['competences_techniques'])): ?>
                                        <div class="detail-item" style="margin-bottom: 10px;">
                                            <span class="detail-label">Techniques</span>
                                            <span class="content-text"><?php echo nl2br(htmlspecialchars($demande['competences_techniques'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($demande['competences_linguistiques'])): ?>
                                        <div class="detail-item" style="margin-bottom: 10px;">
                                            <span class="detail-label">Linguistiques</span>
                                            <span class="content-text"><?php echo nl2br(htmlspecialchars($demande['competences_linguistiques'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($demande['competences_interpersonnelles'])): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Interpersonnelles</span>
                                            <span class="content-text"><?php echo nl2br(htmlspecialchars($demande['competences_interpersonnelles'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Centres d'intérêt -->
                                <?php if (!empty($demande['centres_interet'])): ?>
                                    <div class="content-section">
                                        <div class="content-title">
                                            <i class="fas fa-heart"></i> Centres d'Intérêt
                                        </div>
                                        <div class="content-text"><?php echo nl2br(htmlspecialchars($demande['centres_interet'])); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Commentaires de l'administrateur -->
                            <?php if (!empty($demande['commentaires_admin'])): ?>
                                <div class="commentaires">
                                    <div class="commentaires-title">
                                        <i class="fas fa-comment-dots"></i> Commentaires de l'administrateur
                                    </div>
                                    <div class="content-text"><?php echo nl2br(htmlspecialchars($demande['commentaires_admin'])); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="demande-actions">
                                <?php if ($demande['statut'] == 'en_traitement'): ?>
                                    <button class="btn btn-small btn-danger annuler-btn" 
                                            data-id="<?php echo $demande['id']; ?>">
                                        <i class="fas fa-times"></i> Annuler la demande
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($demande['statut'] == 'termine' && !empty($demande['fichier_cv'])): ?>
                                    <a href="download_cv.php?id=<?php echo $demande['id']; ?>" class="btn btn-small">
                                        <i class="fas fa-download"></i> Télécharger le CV
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-small btn-outline" onclick="afficherDetails(<?php echo $demande['id']; ?>)">
                                    <i class="fas fa-eye"></i> Voir détails
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour afficher les détails d'une demande
        function afficherDetails(demandeId) {
            // Rediriger vers la page de détails
            window.location.href = 'detail_demande_cv.php?id=' + demandeId;
        }

        // Fonction pour annuler une demande
        document.addEventListener('DOMContentLoaded', function() {
            const boutonsAnnulation = document.querySelectorAll('.annuler-btn');
            boutonsAnnulation.forEach(btn => {
                btn.addEventListener('click', function() {
                    const demandeId = this.getAttribute('data-id');
                    
                    if (confirm('Êtes-vous sûr de vouloir annuler cette demande ? Cette action est irréversible.')) {
                        // Envoyer la requête d'annulation
                        const formData = new FormData();
                        formData.append('id', demandeId);
                        formData.append('action', 'annuler');

                        fetch('mes_demandes_cv.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Demande annulée avec succès');
                                location.reload();
                            } else {
                                alert('Erreur lors de l\'annulation : ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erreur lors de l\'annulation');
                        });
                    }
                });
            });
        });

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const demandeItems = document.querySelectorAll('.demande-item');
            demandeItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>

    <?php
    // Traitement de l'annulation
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'annuler') {
        $demandeId = $_POST['id'];
        
        try {
            // Vérifier que la demande appartient bien à l'utilisateur connecté
            $stmt = $pdo->prepare("SELECT user_id FROM demandes_creation_cv WHERE id = ?");
            $stmt->execute([$demandeId]);
            $demande = $stmt->fetch();
            
            if ($demande && $demande['user_id'] == $_SESSION['user_id']) {
                $stmt = $pdo->prepare("UPDATE demandes_creation_cv SET statut = 'annule' WHERE id = ?");
                $stmt->execute([$demandeId]);
                
                echo json_encode(['success' => true, 'message' => 'Demande annulée avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Demande non trouvée ou accès non autorisé']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    ?>
</body>
</html>