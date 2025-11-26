<?php
// admin_demandes.php - Panel d'administration
session_start();

// Vérifier si admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Accès non autorisé");
}

// Connexion DB
$pdo = new PDO("mysql:host=127.0.0.1;dbname=babylone_service", "root", "");

// Récupérer toutes les demandes
$stmt = $pdo->query("SELECT * FROM attestation_province ORDER BY date_soumission DESC");
$demandes = $stmt->fetchAll();

// Changer statut
if ($_POST['action'] === 'changer_statut') {
    $stmt = $pdo->prepare("UPDATE attestation_province SET statut = ?, notes_admin = ?, date_traitement = NOW() WHERE id = ?");
    $stmt->execute([$_POST['statut'], $_POST['notes'], $_POST['id']]);
    header("Location: admin_demandes.php");
    exit;
}
?>

<!-- Interface admin simple -->
<table border="1">
    <tr>
        <th>ID</th><th>Nom</th><th>Province</th><th>Statut</th><th>Date</th><th>Actions</th>
    </tr>
    <?php foreach ($demandes as $d): ?>
    <tr>
        <td><?php echo $d['id']; ?></td>
        <td><?php echo htmlspecialchars($d['nom_complet']); ?></td>
        <td><?php echo htmlspecialchars($d['province']); ?></td>
        <td><?php echo $d['statut']; ?></td>
        <td><?php echo $d['date_soumission']; ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                <select name="statut">
                    <option value="nouveau" <?php echo $d['statut'] === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                    <option value="en_traitement" <?php echo $d['statut'] === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                    <option value="approuve" <?php echo $d['statut'] === 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                    <option value="refuse" <?php echo $d['statut'] === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                </select>
                <input type="text" name="notes" placeholder="Notes">
                <button type="submit" name="action" value="changer_statut">Modifier</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>