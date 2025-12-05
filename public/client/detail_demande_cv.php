<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '../../../config.php';

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mes_demandes_cv.php');
    exit();
}

$demande_id = $_GET['id'];

try {
    

    // Récupérer les détails de la demande
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_creation_cv 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        header('Location: mes_demandes_cv.php');
        exit();
    }

    // Décoder les données JSON
    $formations = !empty($demande['formations']) ? json_decode($demande['formations'], true) : [];
    $experiences = !empty($demande['experiences']) ? json_decode($demande['experiences'], true) : [];

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Fonction pour formater la date
function formaterDate($date) {
    return $date ? date('d/m/Y', strtotime($date)) : 'Non spécifiée';
}

function formaterDateComplete($date) {
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

// Fonction pour afficher une valeur avec gestion du vide
function afficherValeur($valeur) {
    return !empty($valeur) ? htmlspecialchars($valeur) : '<span class="vide">Non renseigné</span>';
}

// Fonction pour formater la durée d'une expérience
function formaterDuree($debut, $fin) {
    if (empty($debut)) return '';
    
    $debut_str = formaterDate($debut);
    $fin_str = empty($fin) ? 'En cours' : formaterDate($fin);
    
    return "Du $debut_str au $fin_str";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Demande CV #<?php echo $demande_id; ?> - Babylone Service</title>
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
            --border: #e1e8ed;
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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

        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #2ecc71);
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

        .demande-details {
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
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

        .card-body {
            padding: 30px;
        }

        .section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--light);
        }

        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--secondary);
        }

        .info-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 500;
        }

        .vide {
            color: #95a5a6;
            font-style: italic;
        }

        .formation-item, .experience-item {
            background: white;
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .formation-item:hover, .experience-item:hover {
            border-color: var(--secondary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .formation-header, .experience-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .formation-titre, .experience-titre {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .formation-details, .experience-details {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .formation-description, .experience-description {
            margin-top: 10px;
            color: #555;
            line-height: 1.5;
        }

        .competences-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .competence-categorie {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .competence-titre {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .competence-content {
            color: #555;
            line-height: 1.6;
        }

        .commentaires-section {
            background: #e8f4fd;
            border: 1px solid #b6d7e8;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
        }

        .commentaires-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .commentaires-content {
            color: #2c3e50;
            line-height: 1.6;
            white-space: pre-line;
        }

        .fichier-section {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
            text-align: center;
        }

        .fichier-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .metadata {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .metadata-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .metadata-item {
            display: flex;
            flex-direction: column;
        }

        .metadata-label {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .metadata-value {
            font-weight: 500;
            color: var(--dark);
        }

        .actions-panel {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light);
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .competences-grid {
                grid-template-columns: 1fr;
            }
            
            .formation-header, .experience-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions-panel {
                flex-direction: column;
            }
            
            .metadata-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Détails de la Demande de CV</h1>
            <p>Informations complètes de votre demande</p>
        </div>

        <div class="navigation">
            <a href="mes_demandes_cv.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <div>
                <?php if ($demande['statut'] == 'en_traitement'): ?>
                    <button class="btn btn-danger annuler-btn" data-id="<?php echo $demande_id; ?>">
                        <i class="fas fa-times"></i> Annuler la demande
                    </button>
                <?php endif; ?>
                
                <?php if ($demande['statut'] == 'termine' && !empty($demande['fichier_cv'])): ?>
                    <a href="download_cv.php?id=<?php echo $demande_id; ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Télécharger le CV
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="demande-details">
            <div class="card-header">
                <h2>
                    <i class="fas fa-file-invoice"></i> 
                    Demande #<?php echo $demande_id; ?>
                </h2>
                <div class="status-badge <?php echo getStatutClass($demande['statut']); ?>">
                    <?php echo getStatutLabel($demande['statut']); ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Section Informations Personnelles -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Informations Personnelles
                    </h3>
                    <div class="info-grid">
                        <div class="info-group">
                            <div class="info-label">Nom complet</div>
                            <div class="info-value"><?php echo afficherValeur($demande['nom_complet']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo afficherValeur($demande['email']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Téléphone</div>
                            <div class="info-value"><?php echo afficherValeur($demande['telephone']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Adresse</div>
                            <div class="info-value"><?php echo afficherValeur($demande['adresse']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Date de naissance</div>
                            <div class="info-value"><?php echo formaterDate($demande['date_naissance']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Nationalité</div>
                            <div class="info-value"><?php echo afficherValeur($demande['nationalite']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Situation familiale</div>
                            <div class="info-value"><?php echo afficherValeur($demande['situation_familiale']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Formations -->
                <?php if (!empty($formations)): ?>
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-graduation-cap"></i> Formations
                        <span class="badge-count"><?php echo count($formations); ?></span>
                    </h3>
                    <?php foreach ($formations as $index => $formation): ?>
                        <div class="formation-item">
                            <div class="formation-header">
                                <div class="formation-titre">
                                    <?php echo afficherValeur($formation['diplome']); ?>
                                </div>
                                <div class="formation-details">
                                    <?php if (!empty($formation['annee_obtention'])): ?>
                                        Année <?php echo htmlspecialchars($formation['annee_obtention']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="formation-details">
                                <strong>Établissement :</strong> 
                                <?php echo afficherValeur($formation['etablissement']); ?>
                            </div>
                            <?php if (!empty($formation['description'])): ?>
                                <div class="formation-description">
                                    <?php echo nl2br(afficherValeur($formation['description'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Section Expériences Professionnelles -->
                <?php if (!empty($experiences)): ?>
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-briefcase"></i> Expériences Professionnelles
                        <span class="badge-count"><?php echo count($experiences); ?></span>
                    </h3>
                    <?php foreach ($experiences as $index => $experience): ?>
                        <div class="experience-item">
                            <div class="experience-header">
                                <div class="experience-titre">
                                    <?php echo afficherValeur($experience['poste']); ?>
                                </div>
                                <div class="experience-details">
                                    <?php echo formaterDuree($experience['date_debut'], $experience['date_fin']); ?>
                                </div>
                            </div>
                            <div class="experience-details">
                                <strong>Entreprise :</strong> 
                                <?php echo afficherValeur($experience['entreprise']); ?>
                            </div>
                            <?php if (!empty($experience['description'])): ?>
                                <div class="experience-description">
                                    <?php echo nl2br(afficherValeur($experience['description'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Section Compétences -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-star"></i> Compétences
                    </h3>
                    <div class="competences-grid">
                        <?php if (!empty($demande['competences_techniques'])): ?>
                        <div class="competence-categorie">
                            <div class="competence-titre">
                                <i class="fas fa-cog"></i> Compétences Techniques
                            </div>
                            <div class="competence-content">
                                <?php echo nl2br(afficherValeur($demande['competences_techniques'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($demande['competences_linguistiques'])): ?>
                        <div class="competence-categorie">
                            <div class="competence-titre">
                                <i class="fas fa-language"></i> Compétences Linguistiques
                            </div>
                            <div class="competence-content">
                                <?php echo nl2br(afficherValeur($demande['competences_linguistiques'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($demande['competences_interpersonnelles'])): ?>
                        <div class="competence-categorie">
                            <div class="competence-titre">
                                <i class="fas fa-users"></i> Compétences Interpersonnelles
                            </div>
                            <div class="competence-content">
                                <?php echo nl2br(afficherValeur($demande['competences_interpersonnelles'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Centres d'Intérêt -->
                <?php if (!empty($demande['centres_interet'])): ?>
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-heart"></i> Centres d'Intérêt
                    </h3>
                    <div class="info-group">
                        <div class="competence-content">
                            <?php echo nl2br(afficherValeur($demande['centres_interet'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section Commentaires Administrateur -->
                <?php if (!empty($demande['commentaires_admin'])): ?>
                <div class="commentaires-section">
                    <div class="commentaires-title">
                        <i class="fas fa-comment-dots"></i> Commentaires de l'Administrateur
                    </div>
                    <div class="commentaires-content">
                        <?php echo nl2br(afficherValeur($demande['commentaires_admin'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section Fichier CV -->
                <?php if ($demande['statut'] == 'termine' && !empty($demande['fichier_cv'])): ?>
                <div class="fichier-section">
                    <div class="fichier-title">
                        <i class="fas fa-file-pdf"></i> Votre CV est prêt !
                    </div>
                    <p>Votre CV professionnel a été créé avec succès. Vous pouvez le télécharger en cliquant sur le bouton ci-dessous.</p>
                    <a href="download_cv.php?id=<?php echo $demande_id; ?>" class="btn btn-success" style="margin-top: 15px;">
                        <i class="fas fa-download"></i> Télécharger le CV
                    </a>
                </div>
                <?php endif; ?>

                <!-- Métadonnées -->
                <div class="metadata">
                    <h4 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-info-circle"></i> Informations de la demande
                    </h4>
                    <div class="metadata-grid">
                        <div class="metadata-item">
                            <span class="metadata-label">Date de création</span>
                            <span class="metadata-value"><?php echo formaterDateComplete($demande['date_creation']); ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Dernière mise à jour</span>
                           <span class="metadata-value"><?php echo !empty($demande['date_maj']) ? formaterDateComplete($demande['date_maj']) : formaterDateComplete($demande['date_creation']); ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Numéro de demande</span>
                            <span class="metadata-value">#<?php echo $demande_id; ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Statut</span>
                            <span class="metadata-value <?php echo getStatutClass($demande['statut']); ?>" style="padding: 4px 12px; border-radius: 15px; font-size: 0.8rem;">
                                <?php echo getStatutLabel($demande['statut']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="actions-panel">
                    <a href="mes_demandes_cv.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                    
                    <?php if ($demande['statut'] == 'en_traitement'): ?>
                        <button class="btn btn-danger annuler-btn" data-id="<?php echo $demande_id; ?>">
                            <i class="fas fa-times"></i> Annuler la demande
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($demande['statut'] == 'termine' && !empty($demande['fichier_cv'])): ?>
                        <a href="download_cv.php?id=<?php echo $demande_id; ?>" class="btn btn-success">
                            <i class="fas fa-download"></i> Télécharger le CV
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
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

                        fetch('detail_demande_cv.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Demande annulée avec succès');
                                window.location.reload();
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

            // Animation d'entrée
            const sections = document.querySelectorAll('.section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
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