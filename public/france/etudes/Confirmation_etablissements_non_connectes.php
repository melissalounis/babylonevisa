<?php
session_start();

// Vérifier si un ID de demande est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: formulaire_etablissements_non_connectes.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de la demande
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_etablissements_non_connectes 
        WHERE id = ?
    ");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée.");
    }

    // Récupérer les fichiers associés
    $stmt_fichiers = $pdo->prepare("
        SELECT * FROM demandes_etablissements_non_connectes_fichiers 
        WHERE demande_id = ?
        ORDER BY type_fichier
    ");
    $stmt_fichiers->execute([$demande_id]);
    $fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater la date
function formatDate($date) {
    if (empty($date)) return 'Non renseigné';
    return date('d/m/Y', strtotime($date));
}

// Fonction pour formater le niveau d'études
function formatNiveauEtudes($niveau) {
    $niveaux = [
        'licence1' => 'Licence 1ère année',
        'licence2' => 'Licence 2ème année',
        'licence3' => 'Licence 3ème année',
        'master1' => 'Master 1ère année',
        'master2' => 'Master 2ème année',
        'doctorat' => 'Doctorat',
        'bts' => 'BTS',
        'dut' => 'DUT',
        'inge' => 'École d\'ingénieurs',
        'commerce' => 'École de commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater le type de fichier
function formatTypeFichier($type) {
    $types = [
        'copie_passeport' => 'Copie du passeport',
        'diplomes' => 'Diplômes',
        'releves_notes' => 'Relevés de notes globaux',
        'lettre_motivation' => 'Lettre de motivation',
        'cv' => 'Curriculum Vitae',
        'attestation_francais' => 'Attestation de français',
        'attestation_acceptation' => 'Attestation d\'acceptation'
    ];
    
    // Gestion des relevés par année (releve_annee_1, releve_annee_2, etc.)
    if (preg_match('/releve_annee_(\d+)/', $type, $matches)) {
        return "Relevé de notes année " . $matches[1];
    }
    
    return $types[$type] ?? $type;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de votre demande</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000a64ff;
            --secondary-color: #222d8bff;
            --accent-color: #3275cdff;
            --light-green: #f0fff0;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
            --success-color: #286ea7ff;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            max-width: 1000px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        header {
            background: linear-gradient(135deg, #0a0064ff, #22328bff);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        header h1 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: white;
        }
        
        .demande-info {
            background: var(--light-green);
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .numero-demande {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--light-green);
        }
        
        .info-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .info-section h3 i {
            margin-right: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #555;
            padding: 8px 12px;
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }
        
        .fichiers-list {
            list-style: none;
        }
        
        .fichiers-list li {
            padding: 10px;
            background: white;
            margin-bottom: 8px;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .fichiers-list li i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .actions {
            text-align: center;
            padding: 30px;
            background: var(--light-green);
            border-top: 1px solid var(--border-color);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #006400, #228B22);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            background: var(--light-green);
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
        }
        
        .statut {
            display: inline-block;
            padding: 5px 15px;
            background: var(--success-color);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                padding: 20px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Demande Soumise avec Succès !</h1>
            <p>Votre demande d'inscription a été enregistrée avec succès</p>
        </header>

        <div class="demande-info">
            <div class="numero-demande">
                Numéro de demande : #<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
            <p>Statut : <span class="statut"><?php echo ucfirst(str_replace('_', ' ', $demande['statut'])); ?></span></p>
            <p>Date de soumission : <?php echo formatDate($demande['date_creation']); ?></p>
        </div>

        <div class="content">
            <!-- Récapitulatif du projet d'études -->
            <div class="info-section">
                <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Pays d'études</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['pays_etudes']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Niveau d'études visé</div>
                        <div class="info-value"><?php echo formatNiveauEtudes($demande['niveau_etudes']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Domaine d'études</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nom de la formation</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['nom_formation']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Établissement</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['etablissement']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de début</div>
                        <div class="info-value"><?php echo formatDate($demande['date_debut']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Durée des études</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['duree_etudes']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="info-section">
                <h3><i class="fas fa-user-graduate"></i> Informations personnelles</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nom</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['nom']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Prénom</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['prenom']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de naissance</div>
                        <div class="info-value"><?php echo formatDate($demande['date_naissance']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Lieu de naissance</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['lieu_naissance']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nationalité</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['nationalite']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['telephone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($demande['adresse'])); ?></div>
                    </div>
                </div>
            </div>

            <!-- Informations sur le passeport -->
            <div class="info-section">
                <h3><i class="fas fa-passport"></i> Passeport</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Numéro de passeport</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['num_passeport']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de délivrance</div>
                        <div class="info-value"><?php echo formatDate($demande['date_delivrance']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date d'expiration</div>
                        <div class="info-value"><?php echo formatDate($demande['date_expiration']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Niveau de français -->
            <div class="info-section">
                <h3><i class="fas fa-language"></i> Niveau de français</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Niveau estimé</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['niveau_francais']) ?: 'Non renseigné'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Test de français passé</div>
                        <div class="info-value"><?php echo $demande['tests_francais'] === 'non' ? 'Non' : 'Oui (' . strtoupper($demande['tests_francais']) . ')'; ?></div>
                    </div>
                    <?php if ($demande['tests_francais'] !== 'non'): ?>
                    <div class="info-item">
                        <div class="info-label">Score/Diplôme</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['score_test']) ?: 'Non renseigné'; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parcours académique -->
            <div class="info-section">
                <h3><i class="fas fa-university"></i> Parcours académique</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Dernier diplôme obtenu</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['dernier_diplome']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Établissement d'origine</div>
                        <div class="info-value"><?php echo htmlspecialchars($demande['etablissement_origine']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Moyenne dernière année</div>
                        <div class="info-value"><?php echo $demande['moyenne_derniere_annee'] ?: 'Non renseignée'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Fichiers déposés -->
            <div class="info-section">
                <h3><i class="fas fa-file-upload"></i> Fichiers déposés</h3>
                <?php if (!empty($fichiers)): ?>
                    <ul class="fichiers-list">
                        <?php foreach ($fichiers as $fichier): ?>
                            <li>
                                <i class="fas fa-file"></i>
                                <?php echo formatTypeFichier($fichier['type_fichier']); ?>
                                <small>(<?php echo date('d/m/Y H:i', strtotime($fichier['date_upload'])); ?>)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun fichier déposé.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions">
            <a href="formulaire_etablissements_non_connectes.php" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Nouvelle demande
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer cette page
            </button>
        </div>

        <footer>
            <p>© 2023 Établissements Non Connectés. Tous droits réservés.</p>
            <p>Pour toute question, contactez-nous à : contact@etablissements-non-connectes.fr</p>
        </footer>
    </div>

    <script>
        // Ajouter un message de confirmation d'impression
        document.addEventListener('DOMContentLoaded', function() {
            // Stocker l'ID de la demande dans le localStorage pour référence
            localStorage.setItem('last_demande_id', '<?php echo $demande_id; ?>');
            
            // Afficher une notification de succès
            if (!sessionStorage.getItem('confirmation_shown')) {
                sessionStorage.setItem('confirmation_shown', 'true');
            }
        });
    </script>
</body>
</html>