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

// Récupérer les détails de la demande
try {
    $stmt = $pdo->prepare("SELECT * FROM demandes_reservation WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        die("Demande non trouvée");
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des détails : " . $e->getMessage());
}

// Traitement de la modification du statut
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'changer_statut') {
        $nouveau_statut = $_POST['nouveau_statut'];
        
        try {
            $stmt = $pdo->prepare("UPDATE demandes_reservation SET status = ? WHERE id = ?");
            $stmt->execute([$nouveau_statut, $demande_id]);
            
            // Recharger les données de la demande
            $stmt = $pdo->prepare("SELECT * FROM demandes_reservation WHERE id = ?");
            $stmt->execute([$demande_id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $message_success = "Statut mis à jour avec succès";
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] == 'mettre_a_jour_prix') {
        $nouveau_prix = $_POST['prix_estime'];
        
        try {
            $stmt = $pdo->prepare("UPDATE demandes_reservation SET prix_estime = ? WHERE id = ?");
            $stmt->execute([$nouveau_prix, $demande_id]);
            
            // Recharger les données de la demande
            $stmt = $pdo->prepare("SELECT * FROM demandes_reservation WHERE id = ?");
            $stmt->execute([$demande_id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $message_success = "Prix mis à jour avec succès";
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de la mise à jour du prix : " . $e->getMessage();
        }
    }
}

// Fonctions utilitaires pour l'affichage
function afficherValeur($valeur) {
    return $valeur ? htmlspecialchars($valeur) : '<span style="color: #999; font-style: italic;">Non renseigné</span>';
}

function formaterDate($date) {
    return $date ? date('d/m/Y', strtotime($date)) : '-';
}

function formaterHeure($heure) {
    return $heure ? date('H:i', strtotime($heure)) : '-';
}

// Labels pour les statuts
$status_labels = [
    'en_attente' => 'En attente',
    'confirmee' => 'Confirmée',
    'validee' => 'Validée',
    'annulee' => 'Annulée'
];

// Labels pour les types d'hébergement
$type_hebergement_labels = [
    'hotel' => 'Hôtel',
    'appartement' => 'Appartement',
    'auberge' => 'Auberge',
    'autre' => 'Autre'
];

// Labels pour les catégories de chambre
$categorie_chambre_labels = [
    'standard' => 'Standard',
    'superieure' => 'Supérieure',
    'deluxe' => 'Deluxe',
    'suite' => 'Suite'
];

// Labels pour les moyens de transport
$moyen_transport_labels = [
    'avion' => 'Avion',
    'train' => 'Train',
    'voiture' => 'Voiture',
    'bus' => 'Bus',
    'autre' => 'Autre'
];

// Labels pour les raisons de séjour
$raison_sejour_labels = [
    'tourisme' => 'Tourisme',
    'affaires' => 'Affaires',
    'familial' => 'Visite familiale',
    'medical' => 'Raison médicale',
    'autre' => 'Autre'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Demande - <?php echo htmlspecialchars($demande['numero_dossier']); ?></title>
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
            max-width: 1200px;
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

        .status-en-attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmee {
            background: #d1edff;
            color: #0c5460;
        }

        .status-validee {
            background: #d4edda;
            color: #155724;
        }

        .status-annulee {
            background: #f8d7da;
            color: #721c24;
        }

        .card-body {
            padding: 30px;
        }

        .section {
            margin-bottom: 40px;
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

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .actions-panel {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            border: 2px dashed #dee2e6;
        }

        .actions-title {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
        }

        .action-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-select, .form-input {
            padding: 10px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .btn-small {
            padding: 10px 20px;
            font-size: 0.9rem;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
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

        .notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .action-form {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Détails de la Demande</h1>
            <p>Informations complètes de la réservation</p>
        </div>

        <div class="navigation">
            <a href="mes_demandes_reservation_hotel.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <div>
                <a href="modifier_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                    <i class="fas fa-edit"></i> Modifier la demande
                </a>
            </div>
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

        <div class="demande-details">
            <div class="card-header">
                <h2>
                    <i class="fas fa-file-invoice"></i> 
                    Demande <?php echo htmlspecialchars($demande['numero_dossier']); ?>
                </h2>
                <div class="status-badge <?php echo 'status-' . str_replace('_', '-', $demande['status']); ?>">
                    <?php echo $status_labels[$demande['status']]; ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Section Informations Client -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Informations Client
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Civilité</div>
                            <div class="info-value"><?php echo afficherValeur($demande['civilite']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Nom</div>
                            <div class="info-value"><?php echo afficherValeur($demande['nom']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Prénom</div>
                            <div class="info-value"><?php echo afficherValeur($demande['prenom']); ?></div>
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
                            <div class="info-label">Nationalité</div>
                            <div class="info-value"><?php echo afficherValeur($demande['nationalite']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Documents -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-passport"></i> Documents d'Identité
                    </h3>
                    <div class="grid-2">
                        <div class="info-group">
                            <div class="info-label">Numéro de passeport</div>
                            <div class="info-value"><?php echo afficherValeur($demande['numero_passeport']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Date d'expiration</div>
                            <div class="info-value"><?php echo formaterDate($demande['date_expiration_passeport']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Séjour -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt"></i> Détails du Séjour
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Date d'arrivée</div>
                            <div class="info-value"><?php echo formaterDate($demande['date_arrivee']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Date de départ</div>
                            <div class="info-value"><?php echo formaterDate($demande['date_depart']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Heure d'arrivée prévue</div>
                            <div class="info-value"><?php echo formaterHeure($demande['heure_arrivee_prevue']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Transport -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-plane"></i> Informations de Transport
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Moyen de transport</div>
                            <div class="info-value">
                                <?php 
                                if ($demande['moyen_transport'] && isset($moyen_transport_labels[$demande['moyen_transport']])) {
                                    echo $moyen_transport_labels[$demande['moyen_transport']];
                                } else {
                                    echo afficherValeur($demande['moyen_transport']);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Numéro de vol/train</div>
                            <div class="info-value"><?php echo afficherValeur($demande['numero_vol_train']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Hébergement -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-hotel"></i> Préférences d'Hébergement
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Type d'hébergement</div>
                            <div class="info-value">
                                <?php 
                                if ($demande['type_hebergement'] && isset($type_hebergement_labels[$demande['type_hebergement']])) {
                                    echo $type_hebergement_labels[$demande['type_hebergement']];
                                } else {
                                    echo afficherValeur($demande['type_hebergement']);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Catégorie de chambre</div>
                            <div class="info-value">
                                <?php 
                                if ($demande['categorie_chambre'] && isset($categorie_chambre_labels[$demande['categorie_chambre']])) {
                                    echo $categorie_chambre_labels[$demande['categorie_chambre']];
                                } else {
                                    echo afficherValeur($demande['categorie_chambre']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Voyageurs -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i> Composition du Groupe
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Nombre d'adultes</div>
                            <div class="info-value"><?php echo $demande['nombre_adultes']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Nombre d'enfants</div>
                            <div class="info-value"><?php echo $demande['nombre_enfants']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Âges des enfants</div>
                            <div class="info-value"><?php echo afficherValeur($demande['ages_enfants']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section Informations Complémentaires -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> Informations Complémentaires
                    </h3>
                    <div class="grid-2">
                        <div class="info-group">
                            <div class="info-label">Raison du séjour</div>
                            <div class="info-value">
                                <?php 
                                if ($demande['raison_sejour'] && isset($raison_sejour_labels[$demande['raison_sejour']])) {
                                    echo $raison_sejour_labels[$demande['raison_sejour']];
                                } else {
                                    echo afficherValeur($demande['raison_sejour']);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Précisions financement</div>
                            <div class="info-value"><?php echo afficherValeur($demande['precisions_financement']); ?></div>
                        </div>
                    </div>
                    
                    <?php if ($demande['demandes_speciales']): ?>
                    <div class="info-group" style="grid-column: 1 / -1;">
                        <div class="info-label">Demandes spéciales</div>
                        <div class="info-value"><?php echo nl2br(afficherValeur($demande['demandes_speciales'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Section Administrative -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-cog"></i> Informations Administratives
                    </h3>
                    <div class="grid-3">
                        <div class="info-group">
                            <div class="info-label">Date de création</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Prix estimé</div>
                            <div class="info-value">
                                <?php 
                                if ($demande['prix_estime']) {
                                    echo number_format($demande['prix_estime'], 2, ',', ' ') . ' €';
                                } else {
                                    echo 'Non estimé';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel d'actions administratives -->
                <div class="actions-panel">
                    <h4 class="actions-title">
                        <i class="fas fa-tools"></i> Actions Administratives
                    </h4>
                    
                    <form method="POST" class="action-form">
                        <input type="hidden" name="action" value="changer_statut">
                        <div class="form-group">
                            <label class="form-label">Changer le statut</label>
                            <select name="nouveau_statut" class="form-select" required>
                                <option value="en_attente" <?php echo $demande['status'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmee" <?php echo $demande['status'] == 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                                <option value="validee" <?php echo $demande['status'] == 'validee' ? 'selected' : ''; ?>>Validée</option>
                                <option value="annulee" <?php echo $demande['status'] == 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prix estimé (€)</label>
                            <input type="number" name="prix_estime" class="form-input" step="0.01" min="0" 
                                   value="<?php echo $demande['prix_estime'] ? $demande['prix_estime'] : ''; ?>" 
                                   placeholder="0.00">
                        </div>
                        <button type="submit" class="btn btn-small">
                            <i class="fas fa-sync-alt"></i> Mettre à jour
                        </button>
                    </form>
                    
                    <form method="POST" class="action-form" style="margin-top: 15px;">
                        <input type="hidden" name="action" value="mettre_a_jour_prix">
                        <div class="form-group" style="grid-column: 1 / 3;">
                            <label class="form-label">Mettre à jour uniquement le prix</label>
                            <input type="number" name="prix_estime" class="form-input" step="0.01" min="0" 
                                   value="<?php echo $demande['prix_estime'] ? $demande['prix_estime'] : ''; ?>" 
                                   placeholder="0.00" required>
                        </div>
                        <button type="submit" class="btn btn-small btn-outline">
                            <i class="fas fa-euro-sign"></i> Mettre à jour le prix
                        </button>
                    </form>
                </div>

                <!-- Notes -->
                <div class="notes">
                    <div class="notes-title">
                        <i class="fas fa-sticky-note"></i> Notes importantes :
                    </div>
                    <ul style="color: #856404; padding-left: 20px;">
                        <li>Cette page affiche tous les détails de la demande de réservation</li>
                        <li>Vous pouvez modifier le statut et le prix estimé via le panel d'actions</li>
                        <li>Les modifications sont enregistrées immédiatement</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Confirmation pour les actions importantes
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (this.querySelector('select[name="nouveau_statut"]')) {
                        const nouveauStatut = this.querySelector('select[name="nouveau_statut"]').value;
                        const ancienStatut = '<?php echo $demande['status']; ?>';
                        
                        if (nouveauStatut !== ancienStatut) {
                            if (!confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                                e.preventDefault();
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>