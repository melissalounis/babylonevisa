<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';
$headerPath = __DIR__ . '/../../includes/header.php';
$footerPath = __DIR__ . '/../../includes/footer.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM demandes WHERE id = ?");
$stmt->execute([$id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php if (file_exists($headerPath)) include $headerPath; ?>

<h2>Suivi de la demande</h2>

<?php if (!$demande): ?>
    <p>⚠️ Demande introuvable.</p>
<?php else: ?>
    <ul>
        <li><strong>ID :</strong> <?= htmlspecialchars($demande['id']) ?></li>
        <li><strong>Pays :</strong> <?= htmlspecialchars($demande['pays']) ?></li>
        <li><strong>Visa :</strong> <?= htmlspecialchars($demande['visa_type']) ?></li>
        <li><strong>Type de demande :</strong> <?= htmlspecialchars($demande['type_demande']) ?></li>
        <li><strong>Nom complet :</strong> <?= htmlspecialchars($demande['nom_complet']) ?></li>
        <li><strong>PDF :</strong> 
            <?php if ($demande['pdf_path']): ?>
                <a href="../../<?= htmlspecialchars($demande['pdf_path']) ?>" target="_blank">📄 Télécharger</a>
            <?php else: ?>
                Aucun fichier
            <?php endif; ?>
        </li>
        <li><strong>Date création :</strong> <?= htmlspecialchars($demande['date_creation']) ?></li>
    </ul>
<?php endif; ?>

<div style="margin-top:20px;">
    <a href="demandes.php">⬅️ Retour à mes demandes</a>
</div>

<?php if (file_exists($footerPath)) include $footerPath; ?>
