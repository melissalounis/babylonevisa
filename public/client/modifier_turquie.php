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
require_once __DIR__ . '../../../config.php';
$erreurs = [];
$success = false;

try {
   
    // Récupérer les détails de la demande
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

    // Vérifier si la demande peut être modifiée (seulement en attente)
    if ($demande['statut'] !== 'en_attente') {
        $_SESSION['erreur'] = "Cette demande ne peut plus être modifiée car son statut est : " . $demande['statut'];
        header("Location: details_turquie.php?id=" . $demande_id);
        exit;
    }

    // Traitement du formulaire de modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et validation des données
        $specialite = trim($_POST['specialite'] ?? '');
        $niveau = trim($_POST['niveau'] ?? '');
        $programme_langue = trim($_POST['programme_langue'] ?? '');
        $certificat_type = trim($_POST['certificat_type'] ?? '');
        $certificat_score = trim($_POST['certificat_score'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        // Informations personnelles
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $date_naissance = trim($_POST['date_naissance'] ?? '');
        $nationalite = trim($_POST['nationalite'] ?? '');

        // Validation
        if (empty($specialite)) {
            $erreurs[] = "La spécialité est obligatoire";
        }
        
        if (empty($niveau)) {
            $erreurs[] = "Le niveau d'études est obligatoire";
        }
        
        if (empty($programme_langue)) {
            $erreurs[] = "La langue du programme est obligatoire";
        }
        
        if (empty($nom)) {
            $erreurs[] = "Le nom est obligatoire";
        }
        
        if (empty($prenom)) {
            $erreurs[] = "Le prénom est obligatoire";
        }

        // Si pas d'erreurs, mise à jour
        if (empty($erreurs)) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("
                    UPDATE demandes_turquie 
                    SET specialite = ?, niveau = ?, programme_langue = ?, 
                        certificat_type = ?, certificat_score = ?, notes = ?,
                        nom = ?, prenom = ?, telephone = ?, date_naissance = ?, nationalite = ?,
                        updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                
                $stmt->execute([
                    $specialite, $niveau, $programme_langue,
                    $certificat_type, $certificat_score, $notes,
                    $nom, $prenom, $telephone, $date_naissance, $nationalite,
                    $demande_id, $user_id
                ]);
                
                $pdo->commit();
                $success = true;
                $_SESSION['success'] = "La demande a été modifiée avec succès";
                
                // Redirection vers la page de détails
                header("Location: details_turquie.php?id=" . $demande_id);
                exit;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $erreurs[] = "Erreur lors de la modification : " . $e->getMessage();
            }
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Demande Turquie - TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></title>
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
            max-width: 1000px;
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
            background: #fff3cd;
            color: #856404;
        }

        .form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .form-header {
            background: var(--light-gray);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-header h2 {
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(227, 10, 23, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .required::after {
            content: " *";
            color: var(--danger-color);
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

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #f8d7da;
            border-left-color: var(--danger-color);
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border-left-color: var(--success-color);
            color: #155724;
        }

        .alert-warning {
            background: #fff3cd;
            border-left-color: var(--warning-color);
            color: #856404;
        }

        .info-box {
            background: #d1ecf1;
            border-left: 4px solid var(--info-color);
            color: #0c5460;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .certificat-fields {
            background: var(--light-gray);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-top: 10px;
            border-left: 3px solid var(--info-color);
        }

        .hidden {
            display: none;
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

            .form-grid {
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
                    <h1><i class="fas fa-edit"></i> Modifier la Demande</h1>
                    <div class="reference">TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="status-badge">
                    <i class="fas fa-clock"></i> En attente - Modifiable
                </div>
            </div>
        </div>

        <!-- Messages d'alerte -->
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-triangle"></i> Erreurs</h4>
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['erreur'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php echo htmlspecialchars($_SESSION['erreur']); 
                      unset($_SESSION['erreur']); ?>
            </div>
        <?php endif; ?>

        <!-- Info box -->
        <div class="info-box">
            <h4><i class="fas fa-info-circle"></i> Informations importantes</h4>
            <p>Vous pouvez modifier votre demande tant qu'elle est en statut "En attente". Toute modification sera soumise à une nouvelle vérification par notre équipe.</p>
        </div>

        <!-- Formulaire de modification -->
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-pencil-alt"></i> Formulaire de modification</h2>
            </div>
            
            <form method="POST" class="form-body">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-user"></i> Informations Personnelles</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom" class="required">Nom</label>
                            <input type="text" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($_POST['nom'] ?? $demande['nom'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="prenom" class="required">Prénom</label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="<?php echo htmlspecialchars($_POST['prenom'] ?? $demande['prenom'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" 
                                   value="<?php echo htmlspecialchars($_POST['telephone'] ?? $demande['telephone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="date_naissance">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" 
                                   value="<?php echo htmlspecialchars($_POST['date_naissance'] ?? $demande['date_naissance'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="nationalite">Nationalité</label>
                            <input type="text" id="nationalite" name="nationalite" 
                                   value="<?php echo htmlspecialchars($_POST['nationalite'] ?? $demande['nationalite'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Informations académiques -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Informations Académiques</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="specialite" class="required">Spécialité demandée</label>
                            <input type="text" id="specialite" name="specialite" 
                                   value="<?php echo htmlspecialchars($_POST['specialite'] ?? $demande['specialite'] ?? ''); ?>" 
                                   required placeholder="Ex: Médecine, Informatique, Business...">
                        </div>

                        <div class="form-group">
                            <label for="niveau" class="required">Niveau d'études</label>
                            <select id="niveau" name="niveau" required>
                                <option value="">Sélectionnez un niveau</option>
                                <option value="bac" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'bac' ? 'selected' : ''; ?>>Baccalauréat</option>
                                <option value="l1" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                                <option value="l2" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                                <option value="l3" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                                <option value="master" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'master' ? 'selected' : ''; ?>>Master</option>
                                <option value="doctorat" <?php echo ($_POST['niveau'] ?? $demande['niveau'] ?? '') === 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="programme_langue" class="required">Langue du programme</label>
                            <select id="programme_langue" name="programme_langue" required>
                                <option value="">Sélectionnez une langue</option>
                                <option value="turc" <?php echo ($_POST['programme_langue'] ?? $demande['programme_langue'] ?? '') === 'turc' ? 'selected' : ''; ?>>Turc</option>
                                <option value="anglais" <?php echo ($_POST['programme_langue'] ?? $demande['programme_langue'] ?? '') === 'anglais' ? 'selected' : ''; ?>>Anglais</option>
                                <option value="bilingue" <?php echo ($_POST['programme_langue'] ?? $demande['programme_langue'] ?? '') === 'bilingue' ? 'selected' : ''; ?>>Bilingue Turc/Anglais</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Certificat de langue -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-language"></i> Certificat de Langue</h3>
                    
                    <div class="form-group">
                        <label for="certificat_type">Type de certificat</label>
                        <select id="certificat_type" name="certificat_type">
                            <option value="sans" <?php echo ($_POST['certificat_type'] ?? $demande['certificat_type'] ?? '') === 'sans' ? 'selected' : ''; ?>>Aucun certificat</option>
                            <option value="tys" <?php echo ($_POST['certificat_type'] ?? $demande['certificat_type'] ?? '') === 'tys' ? 'selected' : ''; ?>>TYS (Test de Turc)</option>
                            <option value="toefl" <?php echo ($_POST['certificat_type'] ?? $demande['certificat_type'] ?? '') === 'toefl' ? 'selected' : ''; ?>>TOEFL</option>
                            <option value="ielts" <?php echo ($_POST['certificat_type'] ?? $demande['certificat_type'] ?? '') === 'ielts' ? 'selected' : ''; ?>>IELTS</option>
                        </select>
                    </div>

                    <div id="certificat_fields" class="certificat-fields <?php echo (($_POST['certificat_type'] ?? $demande['certificat_type'] ?? 'sans') === 'sans') ? 'hidden' : ''; ?>">
                        <div class="form-group">
                            <label for="certificat_score">Score du test</label>
                            <input type="text" id="certificat_score" name="certificat_score" 
                                   value="<?php echo htmlspecialchars($_POST['certificat_score'] ?? $demande['certificat_score'] ?? ''); ?>" 
                                   placeholder="Ex: 85, 6.5, B2...">
                        </div>
                    </div>
                </div>

                <!-- Notes supplémentaires -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-sticky-note"></i> Informations Complémentaires</h3>
                    <div class="form-group">
                        <label for="notes">Notes ou commentaires supplémentaires</label>
                        <textarea id="notes" name="notes" placeholder="Toute information supplémentaire que vous souhaitez ajouter..."><?php echo htmlspecialchars($_POST['notes'] ?? $demande['notes'] ?? $demande['commentaires'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    
                    <a href="details_turquie.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    
                    <a href="mes_demandes_turquie.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </form>
        </div>

        <footer>
            <p>© <?php echo date('Y'); ?> Études Turquie - Babylone Service</p>
            <p>Dernière modification possible uniquement pour les demandes en statut "En attente"</p>
        </footer>
    </div>

    <script>
        // Gestion de l'affichage des champs de certificat
        document.getElementById('certificat_type').addEventListener('change', function() {
            const certificatFields = document.getElementById('certificat_fields');
            if (this.value !== 'sans') {
                certificatFields.classList.remove('hidden');
            } else {
                certificatFields.classList.add('hidden');
            }
        });

        // Confirmation avant de quitter la page si des modifications ont été faites
        let formModified = false;
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                formModified = true;
            });
        });

        form.addEventListener('submit', () => {
            formModified = false;
        });

        window.addEventListener('beforeunload', (e) => {
            if (formModified) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non enregistrées. Êtes-vous sûr de vouloir quitter ?';
            }
        });

        // Message de confirmation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir modifier cette demande ?')) {
                e.preventDefault();
            }
        });

        console.log('Page de modification chargée - Demande TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>');
    </script>
</body>
</html>