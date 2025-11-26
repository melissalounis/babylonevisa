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

// Vérifier si un ID de rendez-vous est passé en paramètre
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Aucun rendez-vous spécifié.";
    header("Location: mes_rendezvous_test_langue.php");
    exit;
}

$rendezvous_id = $_GET['id'];

// Récupérer les données du rendez-vous à modifier
$stmt = $pdo->prepare("SELECT * FROM langue_tests WHERE id = ? AND email = ?");
$stmt->execute([$rendezvous_id, $user_email]);
$rendezvous = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rendezvous) {
    $_SESSION['error_message'] = "Rendez-vous non trouvé ou vous n'avez pas l'autorisation de le modifier.";
    header("Location: mes_rendezvous_test_langue.php");
    exit;
}

// Vérifier que le rendez-vous peut être modifié (seulement s'il est en attente)
if ($rendezvous['statut'] !== 'en_attente') {
    $_SESSION['error_message'] = "Ce rendez-vous ne peut plus être modifié car il est " . $rendezvous['statut'] . ".";
    header("Location: mes_rendezvous_test_langue.php");
    exit;
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_rendezvous'])) {
    // Récupérer les données du formulaire
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $code_postal = $_POST['code_postal'];
    $pays = $_POST['pays'];
    $type_test = $_POST['type_test'];
    $date_rendezvous = $_POST['date_rendezvous'];
    $heure_rendezvous = $_POST['heure_rendezvous'];
    $type_piece = $_POST['type_piece'];
    $numero_piece = $_POST['numero_piece'];
    $date_emission_piece = $_POST['date_emission_piece'];
    $date_expiration_piece = $_POST['date_expiration_piece'];
    
    try {
        // Mettre à jour le rendez-vous
        $stmt = $pdo->prepare("
            UPDATE langue_tests SET 
                prenom = ?, nom = ?, email = ?, telephone = ?, 
                adresse = ?, ville = ?, code_postal = ?, pays = ?,
                type_test = ?, date_rendezvous = ?, heure_rendezvous = ?,
                type_piece = ?, numero_piece = ?, date_emission_piece = ?, date_expiration_piece = ?,
                date_modification = NOW()
            WHERE id = ? AND email = ?
        ");
        
        $stmt->execute([
            $prenom, $nom, $email, $telephone,
            $adresse, $ville, $code_postal, $pays,
            $type_test, $date_rendezvous, $heure_rendezvous,
            $type_piece, $numero_piece, $date_emission_piece, $date_expiration_piece,
            $rendezvous_id, $user_email
        ]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Le rendez-vous a été modifié avec succès.";
        } else {
            $_SESSION['error_message'] = "Aucune modification n'a été apportée.";
        }
        
        header("Location: mes_rendezvous_test_langue.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la modification : " . $e->getMessage();
        header("Location: mes_rendezvous_test_langue.php");
        exit;
    }
}

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Rendez-vous</title>
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
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header p {
            color: #64748b;
            font-size: 1.1rem;
        }

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

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-color: #3b82f6;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        label i {
            color: #667eea;
            width: 20px;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: white;
            border: 2px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .test-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .test-info h3 {
            color: #0369a1;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-info p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .test-info strong {
            color: #1e293b;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #667eea;
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Modifier le Rendez-vous</h1>
            <p>Modifiez les informations de votre test de langue</p>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Vous modifiez le rendez-vous pour : <strong><?= getTestLabel($rendezvous['type_test']) ?></strong>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="modifier_rendezvous" value="1">

            <div class="test-info">
                <h3><i class="fas fa-clipboard-list"></i> Informations du test</h3>
                <p><i class="fas fa-vial"></i> <strong>Test :</strong> <?= getTestLabel($rendezvous['type_test']) ?></p>
                <p><i class="fas fa-clock"></i> <strong>Statut :</strong> En attente</p>
                <p><i class="fas fa-calendar-alt"></i> <strong>Date actuelle :</strong> <?= date('d/m/Y', strtotime($rendezvous['date_rendezvous'])) ?> à <?= date('H:i', strtotime($rendezvous['heure_rendezvous'])) ?></p>
            </div>

            <!-- Section Informations du test -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-vial"></i> Informations du test</h3>
                
                <div class="form-group">
                    <label for="type_test"><i class="fas fa-tasks"></i> Type de test *</label>
                    <select id="type_test" name="type_test" required>
                        <option value="">Sélectionnez un test</option>
                        <optgroup label="Tests de Français">
                            <option value="tcf_tp" <?= $rendezvous['type_test'] == 'tcf_tp' ? 'selected' : '' ?>>TCF Tout Public</option>
                            <option value="tcf_dap" <?= $rendezvous['type_test'] == 'tcf_dap' ? 'selected' : '' ?>>TCF DAP</option>
                            <option value="tcf_anf" <?= $rendezvous['type_test'] == 'tcf_anf' ? 'selected' : '' ?>>TCF ANF</option>
                            <option value="tcf_canada" <?= $rendezvous['type_test'] == 'tcf_canada' ? 'selected' : '' ?>>TCF Canada</option>
                            <option value="tcf_quebec" <?= $rendezvous['type_test'] == 'tcf_quebec' ? 'selected' : '' ?>>TCF Québec</option>
                            <option value="delf_a1" <?= $rendezvous['type_test'] == 'delf_a1' ? 'selected' : '' ?>>DELF A1</option>
                            <option value="delf_a2" <?= $rendezvous['type_test'] == 'delf_a2' ? 'selected' : '' ?>>DELF A2</option>
                            <option value="delf_b1" <?= $rendezvous['type_test'] == 'delf_b1' ? 'selected' : '' ?>>DELF B1</option>
                            <option value="delf_b2" <?= $rendezvous['type_test'] == 'delf_b2' ? 'selected' : '' ?>>DELF B2</option>
                            <option value="dalf_c1" <?= $rendezvous['type_test'] == 'dalf_c1' ? 'selected' : '' ?>>DALF C1</option>
                            <option value="dalf_c2" <?= $rendezvous['type_test'] == 'dalf_c2' ? 'selected' : '' ?>>DALF C2</option>
                            <option value="tef_canada" <?= $rendezvous['type_test'] == 'tef_canada' ? 'selected' : '' ?>>TEF Canada</option>
                            <option value="tef_quebec" <?= $rendezvous['type_test'] == 'tef_quebec' ? 'selected' : '' ?>>TEF Québec</option>
                        </optgroup>
                        <optgroup label="Tests d'Anglais">
                            <option value="ielts_academic" <?= $rendezvous['type_test'] == 'ielts_academic' ? 'selected' : '' ?>>IELTS Academic</option>
                            <option value="ielts_general" <?= $rendezvous['type_test'] == 'ielts_general' ? 'selected' : '' ?>>IELTS General Training</option>
                            <option value="toefl_ibt" <?= $rendezvous['type_test'] == 'toefl_ibt' ? 'selected' : '' ?>>TOEFL iBT</option>
                            <option value="toeic" <?= $rendezvous['type_test'] == 'toeic' ? 'selected' : '' ?>>TOEIC</option>
                            <option value="cambridge_b2" <?= $rendezvous['type_test'] == 'cambridge_b2' ? 'selected' : '' ?>>Cambridge B2 First</option>
                            <option value="cambridge_c1" <?= $rendezvous['type_test'] == 'cambridge_c1' ? 'selected' : '' ?>>Cambridge C1 Advanced</option>
                            <option value="celpip_general" <?= $rendezvous['type_test'] == 'celpip_general' ? 'selected' : '' ?>>CELPIP General</option>
                            <option value="pte_academic" <?= $rendezvous['type_test'] == 'pte_academic' ? 'selected' : '' ?>>PTE Academic</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_rendezvous"><i class="fas fa-calendar-day"></i> Date du rendez-vous *</label>
                        <input type="date" id="date_rendezvous" name="date_rendezvous" 
                               value="<?= $rendezvous['date_rendezvous'] ?>" required
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label for="heure_rendezvous"><i class="fas fa-clock"></i> Heure du rendez-vous *</label>
                        <input type="time" id="heure_rendezvous" name="heure_rendezvous" 
                               value="<?= $rendezvous['heure_rendezvous'] ?>" required>
                    </div>
                </div>
            </div>

            <!-- Section Informations personnelles -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-user-circle"></i> Informations personnelles</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom"><i class="fas fa-user"></i> Prénom *</label>
                        <input type="text" id="prenom" name="prenom" 
                               value="<?= htmlspecialchars($rendezvous['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nom"><i class="fas fa-user"></i> Nom *</label>
                        <input type="text" id="nom" name="nom" 
                               value="<?= htmlspecialchars($rendezvous['nom']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($rendezvous['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone"><i class="fas fa-phone"></i> Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" 
                               value="<?= htmlspecialchars($rendezvous['telephone']) ?>" required>
                    </div>
                </div>
            </div>

            <!-- Section Adresse -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Adresse</h3>
                
                <div class="form-group">
                    <label for="adresse"><i class="fas fa-home"></i> Adresse *</label>
                    <input type="text" id="adresse" name="adresse" 
                           value="<?= htmlspecialchars($rendezvous['adresse']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ville"><i class="fas fa-city"></i> Ville *</label>
                        <input type="text" id="ville" name="ville" 
                               value="<?= htmlspecialchars($rendezvous['ville']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="code_postal"><i class="fas fa-mail-bulk"></i> Code postal *</label>
                        <input type="text" id="code_postal" name="code_postal" 
                               value="<?= htmlspecialchars($rendezvous['code_postal']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pays"><i class="fas fa-globe"></i> Pays *</label>
                    <input type="text" id="pays" name="pays" 
                           value="<?= htmlspecialchars($rendezvous['pays']) ?>" required>
                </div>
            </div>

            <!-- Section Pièce d'identité -->
            <div class="form-section">
                <h3 class="section-title"><i class="fas fa-id-card"></i> Pièce d'identité</h3>
                
                <div class="form-group">
                    <label for="type_piece"><i class="fas fa-address-card"></i> Type de pièce d'identité *</label>
                    <select id="type_piece" name="type_piece" required>
                        <option value="">Sélectionnez une pièce</option>
                        <option value="passeport" <?= $rendezvous['type_piece'] == 'passeport' ? 'selected' : '' ?>>Passeport</option>
                        <option value="carte_identite" <?= $rendezvous['type_piece'] == 'carte_identite' ? 'selected' : '' ?>>Carte d'identité</option>
                        <option value="permis_conduire" <?= $rendezvous['type_piece'] == 'permis_conduire' ? 'selected' : '' ?>>Permis de conduire</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_piece"><i class="fas fa-hashtag"></i> Numéro de la pièce *</label>
                    <input type="text" id="numero_piece" name="numero_piece" 
                           value="<?= htmlspecialchars($rendezvous['numero_piece']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_emission_piece"><i class="fas fa-calendar-plus"></i> Date d'émission *</label>
                        <input type="date" id="date_emission_piece" name="date_emission_piece" 
                               value="<?= $rendezvous['date_emission_piece'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_expiration_piece"><i class="fas fa-calendar-minus"></i> Date d'expiration *</label>
                        <input type="date" id="date_expiration_piece" name="date_expiration_piece" 
                               value="<?= $rendezvous['date_expiration_piece'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="mes_rendezvous_test_langue.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script>
        // Validation de la date
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date_rendezvous');
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;

            // Validation de la date d'expiration
            const expirationInput = document.getElementById('date_expiration_piece');
            const emissionInput = document.getElementById('date_emission_piece');
            
            emissionInput.addEventListener('change', function() {
                if (this.value) {
                    const minExpiration = new Date(this.value);
                    minExpiration.setDate(minExpiration.getDate() + 1);
                    expirationInput.min = minExpiration.toISOString().split('T')[0];
                }
            });

            // Animation des champs au focus
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>