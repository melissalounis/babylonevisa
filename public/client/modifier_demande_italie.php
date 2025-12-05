<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialiser les variables
$demande = null;
$error_message = null;
$success_message = null;
$etablissements = [];
$domaines_etudes = [];
$niveaux_etudes = [];

try {
    // Inclure le fichier de configuration de la base de données
    require_once __DIR__ . '/../../config.php';
    
    // Vérifier si la connexion PDO existe
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Connexion à la base de données non disponible");
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];
    $demande_id = $_GET['id'] ?? 0;

    // Vérifier si l'ID de la demande est fourni
    if (!$demande_id) {
        header("Location: mes_demandes_italie.php");
        exit;
    }

    // Récupérer la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_italie WHERE id = ? AND user_id = ?");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch();

    // Vérifier si la demande existe et appartient à l'utilisateur
    if (!$demande) {
        $error_message = "Demande non trouvée ou vous n'avez pas l'autorisation de la modifier.";
    } elseif ($demande['statut'] !== 'en_attente' && $demande['statut'] !== 'incomplet') {
        $error_message = "Cette demande ne peut plus être modifiée (statut: " . $demande['statut'] . ").";
    }

    // Récupérer les listes déroulantes depuis la base de données
    if (!$error_message) {
        // Récupérer la liste des établissements
        $stmt_etablissements = $pdo->query("SELECT DISTINCT etablissement FROM demandes_italie WHERE etablissement IS NOT NULL ORDER BY etablissement");
        $etablissements = $stmt_etablissements->fetchAll(PDO::FETCH_COLUMN);

        // Récupérer la liste des domaines d'études
        $stmt_domaines = $pdo->query("SELECT DISTINCT domaine_etudes FROM demandes_italie WHERE domaine_etudes IS NOT NULL ORDER BY domaine_etudes");
        $domaines_etudes = $stmt_domaines->fetchAll(PDO::FETCH_COLUMN);

        // Définir les niveaux d'études
        $niveaux_etudes = [
            'licence1' => 'Licence 1',
            'licence2' => 'Licence 2', 
            'licence3' => 'Licence 3',
            'master1' => 'Master 1',
            'master2' => 'Master 2',
            'doctorat' => 'Doctorat'
        ];

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom_formation = trim($_POST['nom_formation'] ?? '');
            $etablissement = trim($_POST['etablissement'] ?? '');
            $ville_etablissement = trim($_POST['ville_etablissement'] ?? '');
            $pays_etablissement = trim($_POST['pays_etablissement'] ?? '');
            $niveau_etudes = $_POST['niveau_etudes'] ?? '';
            $domaine_etudes = trim($_POST['domaine_etudes'] ?? '');
            $date_debut = $_POST['date_debut'] ?? '';
            $date_fin = $_POST['date_fin'] ?? '';
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $pays = trim($_POST['pays'] ?? '');
            $langue_formation = $_POST['langue_formation'] ?? '';
            $type_formation = $_POST['type_formation'] ?? '';
            $commentaire = trim($_POST['commentaire'] ?? '');

            // Validation des champs requis
            $errors = [];
            if (empty($nom_formation)) $errors[] = "Le nom de la formation est requis.";
            if (empty($etablissement)) $errors[] = "Le nom de l'établissement est requis.";
            if (empty($niveau_etudes)) $errors[] = "Le niveau d'études est requis.";
            if (empty($domaine_etudes)) $errors[] = "Le domaine d'études est requis.";

            // Si pas d'erreurs, mettre à jour la demande
            if (empty($errors)) {
                $stmt_update = $pdo->prepare("
                    UPDATE demandes_italie SET
                        nom_formation = ?,
                        etablissement = ?,
                        ville_etablissement = ?,
                        pays_etablissement = ?,
                        niveau_etudes = ?,
                        domaine_etudes = ?,
                        date_debut = ?,
                        date_fin = ?,
                        telephone = ?,
                        email = ?,
                        adresse = ?,
                        code_postal = ?,
                        ville = ?,
                        pays = ?,
                        langue_formation = ?,
                        type_formation = ?,
                        commentaire = ?,
                        date_modification = NOW(),
                        statut = 'en_attente'
                    WHERE id = ? AND user_id = ?
                ");

                $success = $stmt_update->execute([
                    $nom_formation,
                    $etablissement,
                    $ville_etablissement,
                    $pays_etablissement,
                    $niveau_etudes,
                    $domaine_etudes,
                    $date_debut ?: null,
                    $date_fin ?: null,
                    $telephone,
                    $email,
                    $adresse,
                    $code_postal,
                    $ville,
                    $pays,
                    $langue_formation,
                    $type_formation,
                    $commentaire,
                    $demande_id,
                    $user_id
                ]);

                if ($success) {
                    $success_message = "La demande a été modifiée avec succès !";
                    
                    // Récupérer la demande mise à jour
                    $stmt = $pdo->prepare("SELECT * FROM demandes_italie WHERE id = ? AND user_id = ?");
                    $stmt->execute([$demande_id, $user_id]);
                    $demande = $stmt->fetch();
                } else {
                    $error_message = "Une erreur est survenue lors de la modification.";
                }
            } else {
                $error_message = implode("<br>", $errors);
            }
        }
    }

} catch (PDOException $e) {
    $error_message = "Erreur de base de données : " . $e->getMessage();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// Fonction pour traduire le niveau d'études
function traduireNiveau($niveau) {
    $traductions = [
        'licence1' => 'Licence 1',
        'licence2' => 'Licence 2', 
        'licence3' => 'Licence 3',
        'master1' => 'Master 1',
        'master2' => 'Master 2',
        'doctorat' => 'Doctorat'
    ];
    return $traductions[$niveau] ?? ucfirst($niveau);
}

// Fonction pour formater les dates
function formatDateInput($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    return date('Y-m-d', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la demande Italie - Espace client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #008C45;
            --secondary-color: #CD212A;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --white: #ffffff;
            --light-gray: #e9ecef;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --error-color: #dc3545;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-text);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #008C45, #CD212A);
            color: var(--white);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        header h1 i {
            margin-right: 15px;
        }

        .breadcrumb {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .form-container {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--light-gray);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .required::after {
            content: " *";
            color: var(--secondary-color);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 140, 69, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #006935;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 140, 69, 0.3);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-text);
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #dee2e6;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--secondary-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #a61b24;
            transform: translateY(-2px);
        }

        .demande-info {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item strong {
            color: var(--primary-color);
            display: block;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
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
        <header>
            <h1><i class="fas fa-edit"></i> Modifier la demande Italie</h1>
            <p>Modifiez les informations de votre demande d'études en Italie</p>
        </header>

        <!-- Fil d'Ariane -->
        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Tableau de bord</a> &gt; 
            <a href="mes_demandes_italie.php"><i class="fas fa-list"></i> Mes demandes Italie</a> &gt; 
            <span>Modifier la demande #<?= htmlspecialchars($demande['id'] ?? '') ?></span>
        </div>

        <!-- Messages d'alerte -->
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div><?= $error_message ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?= $success_message ?></div>
            </div>
        <?php endif; ?>

        <?php if ($demande && !$error_message): ?>
            <!-- Informations sur la demande -->
            <div class="demande-info">
                <h3><i class="fas fa-info-circle"></i> Informations sur la demande</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Référence:</strong> IT-<?= htmlspecialchars($demande['id']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Date de création:</strong> <?= date('d/m/Y à H:i', strtotime($demande['date_demande'])) ?>
                    </div>
                    <div class="info-item">
                        <strong>Dernière modification:</strong> 
                        <?= $demande['date_modification'] ? date('d/m/Y à H:i', strtotime($demande['date_modification'])) : 'Jamais modifiée' ?>
                    </div>
                    <div class="info-item">
                        <strong>Statut:</strong> 
                        <span style="padding: 4px 8px; border-radius: 12px; background: #fff3cd; color: #856404;">
                            <?= $demande['statut'] === 'en_attente' ? 'En attente' : 'Dossier incomplet' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Formulaire de modification -->
            <form method="POST" action="" class="form-container">
                <!-- Section Informations académiques -->
                <div class="form-section">
                    <h3><i class="fas fa-graduation-cap"></i> Informations académiques</h3>
                    
                    <div class="form-group">
                        <label for="nom_formation" class="required">Nom de la formation</label>
                        <input type="text" id="nom_formation" name="nom_formation" 
                               value="<?= htmlspecialchars($demande['nom_formation'] ?? '') ?>" required>
                        <div class="help-text">Ex: Master en Informatique, Licence en Biologie, etc.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="etablissement" class="required">Établissement</label>
                            <input type="text" id="etablissement" name="etablissement" list="etablissements-list"
                                   value="<?= htmlspecialchars($demande['etablissement'] ?? '') ?>" required>
                            <datalist id="etablissements-list">
                                <?php foreach ($etablissements as $etablissement): ?>
                                    <option value="<?= htmlspecialchars($etablissement) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="help-text">Ex: Université de Rome "La Sapienza"</div>
                        </div>

                        <div class="form-group">
                            <label for="ville_etablissement">Ville de l'établissement</label>
                            <input type="text" id="ville_etablissement" name="ville_etablissement" 
                                   value="<?= htmlspecialchars($demande['ville_etablissement'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="niveau_etudes" class="required">Niveau d'études</label>
                            <select id="niveau_etudes" name="niveau_etudes" required>
                                <option value="">Sélectionnez un niveau</option>
                                <?php foreach ($niveaux_etudes as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($demande['niveau_etudes'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="domaine_etudes" class="required">Domaine d'études</label>
                            <input type="text" id="domaine_etudes" name="domaine_etudes" list="domaines-list"
                                   value="<?= htmlspecialchars($demande['domaine_etudes'] ?? '') ?>" required>
                            <datalist id="domaines-list">
                                <?php foreach ($domaines_etudes as $domaine): ?>
                                    <option value="<?= htmlspecialchars($domaine) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="help-text">Ex: Informatique, Biologie, Droit, etc.</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="langue_formation">Langue de la formation</label>
                            <select id="langue_formation" name="langue_formation">
                                <option value="">Sélectionnez une langue</option>
                                <option value="italien" <?= ($demande['langue_formation'] ?? '') === 'italien' ? 'selected' : '' ?>>Italien</option>
                                <option value="anglais" <?= ($demande['langue_formation'] ?? '') === 'anglais' ? 'selected' : '' ?>>Anglais</option>
                                <option value="bilingue" <?= ($demande['langue_formation'] ?? '') === 'bilingue' ? 'selected' : '' ?>>Bilingue (Italien/Anglais)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="type_formation">Type de formation</label>
                            <select id="type_formation" name="type_formation">
                                <option value="">Sélectionnez un type</option>
                                <option value="presentiel" <?= ($demande['type_formation'] ?? '') === 'presentiel' ? 'selected' : '' ?>>Présentiel</option>
                                <option value="distance" <?= ($demande['type_formation'] ?? '') === 'distance' ? 'selected' : '' ?>>À distance</option>
                                <option value="mixte" <?= ($demande['type_formation'] ?? '') === 'mixte' ? 'selected' : '' ?>>Mixte</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de début prévue</label>
                            <input type="date" id="date_debut" name="date_debut" 
                                   value="<?= formatDateInput($demande['date_debut'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="date_fin">Date de fin prévue</label>
                            <input type="date" id="date_fin" name="date_fin" 
                                   value="<?= formatDateInput($demande['date_fin'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Informations personnelles -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" 
                                   value="<?= htmlspecialchars($demande['telephone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($demande['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse" 
                               value="<?= htmlspecialchars($demande['adresse'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="code_postal">Code postal</label>
                            <input type="text" id="code_postal" name="code_postal" 
                                   value="<?= htmlspecialchars($demande['code_postal'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" 
                                   value="<?= htmlspecialchars($demande['ville'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="pays">Pays</label>
                            <input type="text" id="pays" name="pays" 
                                   value="<?= htmlspecialchars($demande['pays'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Commentaires -->
                <div class="form-section">
                    <h3><i class="fas fa-comment"></i> Commentaires supplémentaires</h3>
                    
                    <div class="form-group">
                        <label for="commentaire">Commentaire (facultatif)</label>
                        <textarea id="commentaire" name="commentaire" 
                                  placeholder="Ajoutez toute information supplémentaire qui pourrait être utile pour le traitement de votre demande..."><?= htmlspecialchars($demande['commentaire'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Actions du formulaire -->
                <div class="form-actions">
                    <a href="mes_demandes_italie.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    
                    <button type="button" class="btn btn-danger" onclick="supprimerDemande()">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        <?php else: ?>
            <!-- Message si la demande n'existe pas ou n'est pas accessible -->
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <?= $error_message ?: "La demande demandée n'existe pas ou vous n'avez pas l'autorisation de la modifier." ?>
                </div>
            </div>
            <div class="form-actions">
                <a href="mes_demandes_italie.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux demandes
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Fonction pour confirmer la suppression
        function supprimerDemande() {
            if (confirm("Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.")) {
                window.location.href = "supprimer_demande_italie.php?id=<?= $demande['id'] ?? '' ?>";
            }
        }

        // Validation des dates
        document.getElementById('date_debut')?.addEventListener('change', function() {
            const dateFin = document.getElementById('date_fin');
            if (dateFin.value && new Date(this.value) > new Date(dateFin.value)) {
                alert("La date de début ne peut pas être postérieure à la date de fin.");
                this.value = '';
            }
        });

        document.getElementById('date_fin')?.addEventListener('change', function() {
            const dateDebut = document.getElementById('date_debut');
            if (dateDebut.value && new Date(dateDebut.value) > new Date(this.value)) {
                alert("La date de fin ne peut pas être antérieure à la date de début.");
                this.value = '';
            }
        });

        // Auto-complétion pour les champs avec datalist
        document.querySelectorAll('input[list]').forEach(input => {
            input.addEventListener('input', function() {
                const datalist = document.getElementById(this.getAttribute('list'));
                if (datalist) {
                    const options = Array.from(datalist.options);
                    const value = this.value.toLowerCase();
                    const matchingOption = options.find(option => 
                        option.value.toLowerCase().includes(value)
                    );
                    if (matchingOption) {
                        this.value = matchingOption.value;
                    }
                }
            });
        });
    </script>
</body>
</html>