<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Réservation Hôtelière</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles CSS modernes et responsives */
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #d1d7f3ff 0%, #ebe7f0ff 100%);
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
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .booking-card {
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
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

        .btn-block {
            display: block;
            width: 100%;
        }

        .section-title {
            color: var(--primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-hotel"></i> Demande de Réservation Hôtelière</h1>
            <p>Remplissez ce formulaire pour que nous puissions traiter votre réservation</p>
        </div>

        <?php
        // Traitement du formulaire
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération des données
            $civilite = htmlspecialchars($_POST['civilite']);
            $nom = htmlspecialchars($_POST['nom']);
            $prenom = htmlspecialchars($_POST['prenom']);
            $email = htmlspecialchars($_POST['email']);
            $telephone = htmlspecialchars($_POST['telephone']);
            $nationalite = htmlspecialchars($_POST['nationalite']);
            $numero_passeport = htmlspecialchars($_POST['numero_passeport']);
            $date_expiration_passeport = $_POST['date_expiration_passeport'];
            
            $date_arrivee = $_POST['date_arrivee'];
            $date_depart = $_POST['date_depart'];
            $heure_arrivee_prevue = $_POST['heure_arrivee_prevue'];
            $moyen_transport = $_POST['moyen_transport'];
            $numero_vol_train = htmlspecialchars($_POST['numero_vol_train']);
            
            $type_hebergement = $_POST['type_hebergement'];
            $categorie_chambre = $_POST['categorie_chambre'];
            $nombre_adultes = $_POST['nombre_adultes'];
            $nombre_enfants = $_POST['nombre_enfants'];
            $ages_enfants = htmlspecialchars($_POST['ages_enfants']);
            
            $demandes_speciales = htmlspecialchars($_POST['demandes_speciales']);
            $raison_sejour = htmlspecialchars($_POST['raison_sejour']);
            $precisions_financement = htmlspecialchars($_POST['precisions_financement']);
            
            // Validation basique
            $erreurs = [];
            if (empty($nom)) $erreurs[] = "Le nom est requis";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email valide requis";
            if (empty($date_arrivee) || empty($date_depart)) $erreurs[] = "Les dates de séjour sont requises";
            
            if (empty($erreurs)) {
                // Génération d'un numéro de dossier
                $numero_dossier = "DOS" . date('Ymd') . rand(1000, 9999);
                $message_success = "Votre demande a été enregistrée! Votre numéro de dossier: <strong>$numero_dossier</strong>. Nous vous contacterons pour finaliser la réservation.";
            }
        }
        ?>

        <div class="booking-card">
            <div class="card-header">
                <h2><i class="fas fa-user-check"></i> Formulaire de Demande de Réservation</h2>
            </div>
            <div class="card-body">
                <?php if (isset($message_success)): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                        <?php echo $message_success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreurs)): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <strong>Veuillez corriger les erreurs suivantes:</strong>
                        <ul>
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?php echo $erreur; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <h3 class="section-title"><i class="fas fa-user"></i> Informations Personnelles</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="civilite">Civilité</label>
                            <select id="civilite" name="civilite" class="form-control" required>
                                <option value="M">Monsieur</option>
                                <option value="Mme">Madame</option>
                                <option value="Mlle">Mademoiselle</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom">Nom*</label>
                            <input type="text" id="nom" name="nom" class="form-control" required 
                                   value="<?php echo isset($nom) ? $nom : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom*</label>
                            <input type="text" id="prenom" name="prenom" class="form-control" required
                                   value="<?php echo isset($prenom) ? $prenom : ''; ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?php echo isset($email) ? $email : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone*</label>
                            <input type="tel" id="telephone" name="telephone" class="form-control" required
                                   value="<?php echo isset($telephone) ? $telephone : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="nationalite">Nationalité*</label>
                            <input type="text" id="nationalite" name="nationalite" class="form-control" required
                                   value="<?php echo isset($nationalite) ? $nationalite : ''; ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="numero_passeport">Numéro de passeport*</label>
                            <input type="text" id="numero_passeport" name="numero_passeport" class="form-control" required
                                   value="<?php echo isset($numero_passeport) ? $numero_passeport : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_expiration_passeport">Date d'expiration du passeport*</label>
                            <input type="date" id="date_expiration_passeport" name="date_expiration_passeport" class="form-control" required
                                   value="<?php echo isset($date_expiration_passeport) ? $date_expiration_passeport : ''; ?>">
                        </div>
                    </div>

                    <h3 class="section-title"><i class="fas fa-calendar-alt"></i> Détails du Séjour</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date_arrivee">Date d'arrivée*</label>
                            <input type="date" id="date_arrivee" name="date_arrivee" class="form-control" required
                                   value="<?php echo isset($date_arrivee) ? $date_arrivee : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_depart">Date de départ*</label>
                            <input type="date" id="date_depart" name="date_depart" class="form-control" required
                                   value="<?php echo isset($date_depart) ? $date_depart : ''; ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="heure_arrivee_prevue">Heure d'arrivée prévue</label>
                            <input type="time" id="heure_arrivee_prevue" name="heure_arrivee_prevue" class="form-control"
                                   value="<?php echo isset($heure_arrivee_prevue) ? $heure_arrivee_prevue : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="moyen_transport">Moyen de transport</label>
                            <select id="moyen_transport" name="moyen_transport" class="form-control">
                                <option value="avion">Avion</option>
                                <option value="train">Train</option>
                                <option value="voiture">Voiture</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_vol_train">Numéro de vol/train</label>
                            <input type="text" id="numero_vol_train" name="numero_vol_train" class="form-control"
                                   value="<?php echo isset($numero_vol_train) ? $numero_vol_train : ''; ?>">
                        </div>
                    </div>

                    <h3 class="section-title"><i class="fas fa-bed"></i> Préférences d'Hébergement</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_hebergement">Type d'hébergement souhaité</label>
                            <select id="type_hebergement" name="type_hebergement" class="form-control">
                                <option value="hotel">Hôtel</option>
                                <option value="appartement">Appartement</option>
                              <option value="auberge">Auberge</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie_chambre">Catégorie de chambre</label>
                            <select id="categorie_chambre" name="categorie_chambre" class="form-control">
                                <option value="standard">Standard</option>
                                <option value="superieure">Supérieure</option>
                                <option value="suite">Suite</option>
                                <option value="familiale">Familiale</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_adultes">Nombre d'adultes*</label>
                            <select id="nombre_adultes" name="nombre_adultes" class="form-control" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo (isset($nombre_adultes) && $nombre_adultes == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> adulte<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre_enfants">Nombre d'enfants</label>
                            <select id="nombre_enfants" name="nombre_enfants" class="form-control">
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo (isset($nombre_enfants) && $nombre_enfants == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> enfant<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ages_enfants">Âges des enfants</label>
                            <input type="text" id="ages_enfants" name="ages_enfants" class="form-control" 
                                   placeholder="Ex: 3 ans, 7 ans"
                                   value="<?php echo isset($ages_enfants) ? $ages_enfants : ''; ?>">
                        </div>
                    </div>
                    <div class="note-info">
    <i class="fas fa-info-circle"></i> <strong>Note :</strong> Un adulte est considéré comme une personne âgée de 12 ans ou plus. Les enfants de moins de 12 ans doivent être comptés dans la catégorie "enfants".
</div>

                    <h3 class="section-title"><i class="fas fa-file-alt"></i> Informations Complémentaires</h3>
                    <div class="form-group">
                        <label for="demandes_speciales">Demandes spéciales</label>
                        <textarea id="demandes_speciales" name="demandes_speciales" class="form-control" rows="4" 
                                  placeholder="Préférences particulières, besoins spécifiques, régimes alimentaires, accessibilité..."><?php echo isset($demandes_speciales) ? $demandes_speciales : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="raison_sejour">Raison du séjour</label>
                        <select id="raison_sejour" name="raison_sejour" class="form-control">
                            <option value="tourisme">Tourisme</option>
                            <option value="affaires">Voyage d'affaires</option>
                            <option value="familial">Visite familiale</option>
                            <option value="etudes">Études</option>
                            <option value="medical">Raison médicale</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="precisions_financement">Précisions sur le financement du séjour</label>
                        <textarea id="precisions_financement" name="precisions_financement" class="form-control" rows="3" 
                                  placeholder="Précisez si nécessaire comment le séjour sera financé..."><?php echo isset($precisions_financement) ? $precisions_financement : ''; ?></textarea>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <label style="display: flex; align-items: start; gap: 10px; font-size: 0.9rem;">
                            <input type="checkbox" required style="margin-top: 3px; transform: scale(1.2);">
                            <span>J'accepte que mes informations soient utilisées pour traiter ma demande de réservation et confirme que les renseignements fournis sont exacts.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-block" style="margin-top: 20px;">
                        <i class="fas fa-paper-plane"></i> Soumettre ma Demande de Réservation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Définir la date minimale (aujourd'hui)
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_arrivee').min = today;
        document.getElementById('date_depart').min = today;

        document.getElementById('date_arrivee').addEventListener('change', function() {
            document.getElementById('date_depart').min = this.value;
        });
    </script>
</body>
</html>