<?php
// index.php - Formulaire Visa Regroupement Familial Belgique

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<h2>Résumé de la demande de visa regroupement familial :</h2>";
    echo "<strong>Nom :</strong> " . htmlspecialchars($_POST['nom']) . "<br>";
    echo "<strong>Prénom :</strong> " . htmlspecialchars($_POST['prenom']) . "<br>";
    echo "<strong>Date de naissance :</strong> " . htmlspecialchars($_POST['naissance']) . "<br>";
    echo "<strong>Nationalité :</strong> " . htmlspecialchars($_POST['nationalite']) . "<br>";
    echo "<strong>Email :</strong> " . htmlspecialchars($_POST['email']) . "<br>";
    echo "<strong>Téléphone :</strong> " . htmlspecialchars($_POST['telephone']) . "<br>";
    echo "<strong>Lien de parenté :</strong> " . htmlspecialchars($_POST['lien_parent']) . "<br>";
    echo "<strong>Nom du membre de famille en Belgique :</strong> " . htmlspecialchars($_POST['nom_famille']) . "<br>";
    echo "<strong>Statut en Belgique :</strong> " . htmlspecialchars($_POST['statut_belgique']) . "<br>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visa Regroupement Familial - Belgique</title>
</head>
<body>
    <h1>Demande de Visa Regroupement Familial - Belgique</h1>

    <form method="post" enctype="multipart/form-data">
        <h2>Informations personnelles</h2>
        <label>Nom : <input type="text" name="nom" required></label><br><br>
        <label>Prénom : <input type="text" name="prenom" required></label><br><br>
        <label>Date de naissance : <input type="date" name="naissance" required></label><br><br>
        <label>Nationalité : <input type="text" name="nationalite" required></label><br><br>
        <label>Email : <input type="email" name="email" required></label><br><br>
        <label>Téléphone : <input type="tel" name="telephone" required></label><br><br>

        <h2>Informations sur le regroupement familial</h2>
        <label>Lien de parenté (époux(se), enfant, parent...) :
            <input type="text" name="lien_parent" required>
        </label><br><br>
        <label>Nom du membre de famille en Belgique :
            <input type="text" name="nom_famille" required>
        </label><br><br>
        <label>Statut du membre de famille en Belgique :
            <select name="statut_belgique" required>
                <option value="citoyen">Citoyen belge</option>
                <option value="resident">Résident avec permis de séjour</option>
                <option value="refugie">Réfugié reconnu</option>
            </select>
        </label><br><br>

        <h2>Documents requis</h2>
        <label>Formulaire de demande rempli : <input type="file" name="formulaire" required></label><br><br>
        <label>Copie du passeport valide : <input type="file" name="passeport" required></label><br><br>
        <label>Extrait d’acte de naissance : <input type="file" name="naissance_doc" required></label><br><br>
        <label>Preuve du lien de parenté (mariage, naissance...) : <input type="file" name="preuve_lien" required></label><br><br>
        <label>Copie de la carte d’identité/séjour du membre de famille en Belgique : <input type="file" name="id_belgique" required></label><br><br>
        <label>Attestation de logement en Belgique : <input type="file" name="logement" required></label><br><br>
        <label>Preuve de moyens financiers du membre de famille : <input type="file" name="finances" required></label><br><br>
        <label>Assurance maladie couvrant la Belgique : <input type="file" name="assurance" required></label><br><br>

        <button type="submit">Soumettre la demande</button>
    </form>
</body>
</html>
