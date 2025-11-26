<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mes_demandes_reservation_hotel.php');
    exit;
}

$demande_id = $_GET['id'];

// Récupérer les données actuelles de la demande
try {
    $stmt = $pdo->prepare("SELECT * FROM demandes_reservation WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        die("Demande non trouvée");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et validation des données
    $civilite = $_POST['civilite'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $nationalite = trim($_POST['nationalite']);
    $numero_passeport = trim($_POST['numero_passeport']);
    $date_expiration_passeport = $_POST['date_expiration_passeport'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $heure_arrivee_prevue = $_POST['heure_arrivee_prevue'];
    $moyen_transport = $_POST['moyen_transport'];
    $numero_vol_train = trim($_POST['numero_vol_train']);
    $type_hebergement = $_POST['type_hebergement'];
    $categorie_chambre = $_POST['categorie_chambre'];
    $nombre_adultes = intval($_POST['nombre_adultes']);
    $nombre_enfants = intval($_POST['nombre_enfants']);
    $ages_enfants = trim($_POST['ages_enfants']);
    $demandes_speciales = trim($_POST['demandes_speciales']);
    $raison_sejour = $_POST['raison_sejour'];
    $precisions_financement = trim($_POST['precisions_financement']);
    
    // Validation basique
    $erreurs = [];
    
    if (empty($nom)) $erreurs[] = "Le nom est obligatoire";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email n'est pas valide";
    if (empty($telephone)) $erreurs[] = "Le téléphone est obligatoire";
    if (empty($numero_passeport)) $erreurs[] = "Le numéro de passeport est obligatoire";
    if (empty($date_arrivee)) $erreurs[] = "La date d'arrivée est obligatoire";
    if (empty($date_depart)) $erreurs[] = "La date de départ est obligatoire";
    if ($nombre_adultes < 1) $erreurs[] = "Le nombre d'adultes doit être au moins de 1";
    
    // Vérifier que la date de départ est après la date d'arrivée
    if ($date_arrivee && $date_depart && $date_depart <= $date_arrivee) {
        $erreurs[] = "La date de départ doit être après la date d'arrivée";
    }
    
    // Si pas d'erreurs, mettre à jour la base de données
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE demandes_reservation SET
                    civilite = ?,
                    nom = ?,
                    prenom = ?,
                    email = ?,
                    telephone = ?,
                    nationalite = ?,
                    numero_passeport = ?,
                    date_expiration_passeport = ?,
                    date_arrivee = ?,
                    date_depart = ?,
                    heure_arrivee_prevue = ?,
                    moyen_transport = ?,
                    numero_vol_train = ?,
                    type_hebergement = ?,
                    categorie_chambre = ?,
                    nombre_adultes = ?,
                    nombre_enfants = ?,
                    ages_enfants = ?,
                    demandes_speciales = ?,
                    raison_sejour = ?,
                    precisions_financement = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $civilite, $nom, $prenom, $email, $telephone, $nationalite,
                $numero_passeport, $date_expiration_passeport, $date_arrivee, $date_depart,
                $heure_arrivee_prevue, $moyen_transport, $numero_vol_train, $type_hebergement,
                $categorie_chambre, $nombre_adultes, $nombre_enfants, $ages_enfants,
                $demandes_speciales, $raison_sejour, $precisions_financement, $demande_id
            ]);
            
            $message_success = "Demande modifiée avec succès !";
            
            // Recharger les données mises à jour
            $stmt = $pdo->prepare("SELECT * FROM demandes_reservation WHERE id = ?");
            $stmt->execute([$demande_id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}

// Fonction pour sélectionner l'option dans les select
function selected($value, $compare) {
    return $value == $compare ? 'selected' : '';
}

// Fonction pour vérifier les radio buttons
function checked($value, $compare) {
    return $value == $compare ? 'checked' : '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Demande - <?php echo htmlspecialchars($demande['numero_dossier']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #7991fdff 0%, #ebe7f0ff 100%);
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

        .form-container {
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

        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: auto;
        }

        .status-en-attente {
            background: #fff3cd;
            color: #856404;
        }

        .card-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--light);
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 20px;
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
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger);
        }

        .form-input, .form-select, .form-textarea {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-option input[type="radio"] {
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light);
        }

        .btn-large {
            padding: 15px 30px;
            font-size: 1.1rem;
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-list {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .error-list ul {
            margin-left: 20px;
        }

        .form-help {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Modifier la Demande</h1>
            <p>Mettez à jour les informations de votre réservation</p>
        </div>

        <div class="navigation">
            <a href="mes_demandes_reservation_hotel.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <a href="detail_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                <i class="fas fa-eye"></i> Voir les détails
            </a>
        </div>

        <?php if (isset($message_success)): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i> <?php echo $message_success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($message_erreur)): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $message_erreur; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="error-list">
                <strong><i class="fas fa-exclamation-triangle"></i> Veuillez corriger les erreurs suivantes :</strong>
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="card-header">
                <h2>
                    <i class="fas fa-file-invoice"></i> 
                    Demande <?php echo htmlspecialchars($demande['numero_dossier']); ?>
                </h2>
                <div class="status-badge status-en-attente">
                    Statut : <?php echo htmlspecialchars($demande['status']); ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Section Informations Personnelles -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Informations Personnelles
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Civilité</label>
                            <select name="civilite" class="form-select" required>
                                <option value="M." <?php echo selected('M.', $demande['civilite']); ?>>Monsieur</option>
                                <option value="Mme" <?php echo selected('Mme', $demande['civilite']); ?>>Madame</option>
                                <option value="Mlle" <?php echo selected('Mlle', $demande['civilite']); ?>>Mademoiselle</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Nom</label>
                            <input type="text" name="nom" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['nom']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Prénom</label>
                            <input type="text" name="prenom" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['prenom']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Téléphone</label>
                            <input type="tel" name="telephone" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['telephone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Nationalité</label>
                            <input type="text" name="nationalite" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['nationalite']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Section Documents d'Identité -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-passport"></i> Documents d'Identité
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Numéro de passeport</label>
                            <input type="text" name="numero_passeport" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['numero_passeport']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Date d'expiration</label>
                            <input type="date" name="date_expiration_passeport" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['date_expiration_passeport']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Section Dates du Séjour -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt"></i> Dates du Séjour
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Date d'arrivée</label>
                            <input type="date" name="date_arrivee" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['date_arrivee']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Date de départ</label>
                            <input type="date" name="date_depart" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['date_depart']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Heure d'arrivée prévue</label>
                            <input type="time" name="heure_arrivee_prevue" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['heure_arrivee_prevue']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Transport -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-plane"></i> Transport
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Moyen de transport</label>
                            <select name="moyen_transport" class="form-select">
                                <option value="">Sélectionnez...</option>
                                <option value="avion" <?php echo selected('avion', $demande['moyen_transport']); ?>>Avion</option>
                                <option value="train" <?php echo selected('train', $demande['moyen_transport']); ?>>Train</option>
                                <option value="voiture" <?php echo selected('voiture', $demande['moyen_transport']); ?>>Voiture</option>
                                <option value="bus" <?php echo selected('bus', $demande['moyen_transport']); ?>>Bus</option>
                                <option value="autre" <?php echo selected('autre', $demande['moyen_transport']); ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Numéro de vol/train</label>
                            <input type="text" name="numero_vol_train" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['numero_vol_train']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Hébergement -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-hotel"></i> Hébergement
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Type d'hébergement</label>
                            <select name="type_hebergement" class="form-select">
                                <option value="">Sélectionnez...</option>
                                <option value="hotel" <?php echo selected('hotel', $demande['type_hebergement']); ?>>Hôtel</option>
                                <option value="appartement" <?php echo selected('appartement', $demande['type_hebergement']); ?>>Appartement</option>
                                <option value="auberge" <?php echo selected('auberge', $demande['type_hebergement']); ?>>Auberge</option>
                                <option value="autre" <?php echo selected('autre', $demande['type_hebergement']); ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Catégorie de chambre</label>
                            <select name="categorie_chambre" class="form-select">
                                <option value="">Sélectionnez...</option>
                                <option value="standard" <?php echo selected('standard', $demande['categorie_chambre']); ?>>Standard</option>
                                <option value="superieure" <?php echo selected('superieure', $demande['categorie_chambre']); ?>>Supérieure</option>
                                <option value="deluxe" <?php echo selected('deluxe', $demande['categorie_chambre']); ?>>Deluxe</option>
                                <option value="suite" <?php echo selected('suite', $demande['categorie_chambre']); ?>>Suite</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Composition du Groupe -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i> Composition du Groupe
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Nombre d'adultes</label>
                            <input type="number" name="nombre_adultes" class="form-input" min="1" max="10" 
                                   value="<?php echo htmlspecialchars($demande['nombre_adultes']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nombre d'enfants</label>
                            <input type="number" name="nombre_enfants" class="form-input" min="0" max="10" 
                                   value="<?php echo htmlspecialchars($demande['nombre_enfants']); ?>">
                            <div class="form-help">Laissez 0 si aucun enfant</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Âges des enfants</label>
                            <input type="text" name="ages_enfants" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['ages_enfants']); ?>" 
                                   placeholder="Ex: 5, 8, 12">
                            <div class="form-help">Séparez les âges par des virgules</div>
                        </div>
                    </div>
                </div>

                <!-- Section Informations Complémentaires -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> Informations Complémentaires
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Raison du séjour</label>
                            <select name="raison_sejour" class="form-select">
                                <option value="">Sélectionnez...</option>
                                <option value="tourisme" <?php echo selected('tourisme', $demande['raison_sejour']); ?>>Tourisme</option>
                                <option value="affaires" <?php echo selected('affaires', $demande['raison_sejour']); ?>>Affaires</option>
                                <option value="familial" <?php echo selected('familial', $demande['raison_sejour']); ?>>Visite familiale</option>
                                <option value="medical" <?php echo selected('medical', $demande['raison_sejour']); ?>>Raison médicale</option>
                                <option value="autre" <?php echo selected('autre', $demande['raison_sejour']); ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Précisions financement</label>
                            <input type="text" name="precisions_financement" class="form-input" 
                                   value="<?php echo htmlspecialchars($demande['precisions_financement']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label class="form-label">Demandes spéciales</label>
                        <textarea name="demandes_speciales" class="form-textarea" 
                                  placeholder="Toute demande particulière (régime alimentaire, accessibilité, etc.)"><?php echo htmlspecialchars($demande['demandes_speciales']); ?></textarea>
                    </div>
                </div>

                <!-- Actions du formulaire -->
                <div class="form-actions">
                    <a href="detail_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-large">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Validation côté client
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const dateArrivee = document.querySelector('input[name="date_arrivee"]');
            const dateDepart = document.querySelector('input[name="date_depart"]');
            
            // Validation des dates
            function validerDates() {
                if (dateArrivee.value && dateDepart.value) {
                    const arrivee = new Date(dateArrivee.value);
                    const depart = new Date(dateDepart.value);
                    
                    if (depart <= arrivee) {
                        dateDepart.setCustomValidity('La date de départ doit être après la date d\'arrivée');
                    } else {
                        dateDepart.setCustomValidity('');
                    }
                }
            }
            
            dateArrivee.addEventListener('change', validerDates);
            dateDepart.addEventListener('change', validerDates);
            
            // Confirmation avant de quitter si des modifications ont été faites
            let formModified = false;
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    formModified = true;
                });
            });
            
            window.addEventListener('beforeunload', (e) => {
                if (formModified) {
                    e.preventDefault();
                    e.returnValue = 'Vous avez des modifications non enregistrées. Êtes-vous sûr de vouloir quitter ?';
                }
            });
            
            form.addEventListener('submit', () => {
                formModified = false;
            });
        });
    </script>
</body>
</html>