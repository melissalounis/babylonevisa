<?php
session_start();

// Connexion à la base
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

$errors = [];

// Traitement après soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $education = $_POST['education'] ?? '';
    $experience = intval($_POST['experience'] ?? 0);
    $french = intval($_POST['french'] ?? 0);
    $english = intval($_POST['english'] ?? 0);
    $situation_familiale = $_POST['situation_familiale'] ?? '';
    $enfants = intval($_POST['enfants'] ?? 0);
    $offre_emploi = $_POST['offre_emploi'] ?? 'non';
    $famille_canada = $_POST['famille_canada'] ?? 'non';
    $consentement = isset($_POST['consentement']) ? 'oui' : 'non';

    if (empty($nom)) $errors[] = "Le nom est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($age < 18 || $age > 100) $errors[] = "Âge invalide (18-100).";
    if (empty($education)) $errors[] = "Le niveau d'éducation est requis.";
    if (empty($situation_familiale)) $errors[] = "La situation familiale est requise.";
    if ($consentement === 'non') $errors[] = "Vous devez accepter les conditions.";

    if (empty($errors)) {
        $score = calculateArrimaPoints($age, $education, $experience, $french, $english, $situation_familiale, $enfants, $offre_emploi, $famille_canada);

        $seuil = ($situation_familiale === 'marie') ? 59 : 50;
        $is_eligible = $score >= $seuil ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO evaluations_immigration 
            (nom, email, age, education, experience, english_level, french_level, 
             situation_familiale, enfants, province, offre_emploi, famille_canada, 
             programme, score, eligible) 
            VALUES (:nom, :email, :age, :education, :experience, :english, :french,
             :situation_familiale, :enfants, 'Quebec', :offre_emploi, :famille_canada,
             :programme, :score, :eligible)");

        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':age' => $age,
            ':education' => $education,
            ':experience' => $experience,
            ':english' => $english,
            ':french' => $french,
            ':situation_familiale' => $situation_familiale,
            ':enfants' => $enfants,
            ':offre_emploi' => $offre_emploi,
            ':famille_canada' => $famille_canada,
            ':programme' => 'arrima',
            ':score' => $score,
            ':eligible' => $is_eligible
        ]);

        $_SESSION['evaluation_score'] = $score;
        $_SESSION['is_eligible'] = $is_eligible;
        $_SESSION['seuil'] = $seuil;

        header("Location: resultat_arrima.php");
        exit;
    }
}

// Fonction de calcul Arrima
function calculateArrimaPoints($age, $education, $experience, $french, $english, $situation, $enfants, $offre, $famille) {
    $points = 0;

    // Âge
    if ($age >= 18 && $age <= 35) $points += 16;
    elseif ($age == 36) $points += 14;
    elseif ($age == 37) $points += 12;
    elseif ($age == 38) $points += 10;
    elseif ($age == 39) $points += 8;
    elseif ($age == 40) $points += 6;
    elseif ($age >= 41 && $age <= 42) $points += 4;
    elseif ($age >= 43 && $age <= 44) $points += 2;

    // Éducation
    $educationPoints = [
        'secondary' => 2,
        'college' => 6,
        'bachelor' => 10,
        'master' => 12,
        'phd' => 14
    ];
    $points += $educationPoints[$education] ?? 0;

    // Expérience
    if ($experience >= 4) $points += 8;
    elseif ($experience >= 2) $points += 6;
    elseif ($experience >= 1) $points += 4;

    // Langues
    if ($french >= 7) $points += 16; // priorité au français
    if ($english >= 7) $points += 6;

    // Enfants
    $points += $enfants * 2;

    // Offre d'emploi
    if ($offre === 'oui') $points += 8;

    // Famille au Québec
    if ($famille === 'oui') $points += 3;

    // Bonus situation
    if ($situation === 'marie') $points += 2;

    return $points;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Évaluation — Arrima (Québec)</title>
  <style>
/* 🌐 Thème Arrima — Québec */
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(135deg, #e3f2fd, #ffffff);
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* 📦 Conteneur du formulaire */
.form-container {
  max-width: 700px;
  width: 100%;
  background: #fff;
  padding: 35px 40px;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-top: 6px solid #1565c0;
  position: relative;
  animation: fadeIn 0.8s ease-in-out;
}

/* ❄️ Icône décorative Québec */
.form-container::before {
  content: "❄️";
  font-size: 42px;
  position: absolute;
  top: -25px;
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  padding: 5px 15px;
  border-radius: 50%;
  border: 3px solid #1565c0;
}

/* 📝 Titre */
h2 {
  text-align: center;
  color: #0d47a1;
  font-size: 28px;
  margin-bottom: 20px;
  font-weight: bold;
}

/* 🏷 Labels */
label {
  display: block;
  margin: 12px 0 6px;
  font-weight: 600;
  color: #333;
}

/* 🔘 Inputs & Select */
input, select {
  width: 100%;
  padding: 12px;
  border: 1.5px solid #ccc;
  border-radius: 10px;
  outline: none;
  font-size: 15px;
  transition: all 0.3s ease;
}

input:focus, select:focus {
  border-color: #1565c0;
  box-shadow: 0 0 8px rgba(21, 101, 192, 0.3);
}

/* ✅ Checkbox */
input[type="checkbox"] {
  width: auto;
  margin-right: 8px;
  transform: scale(1.2);
}

/* 🔵 Bouton Québec */
.btn-submit {
  background: linear-gradient(135deg, #1565c0, #0d47a1);
  color: #fff;
  padding: 14px;
  width: 100%;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 17px;
  font-weight: bold;
  transition: all 0.3s ease;
  margin-top: 15px;
}

.btn-submit:hover {
  background: linear-gradient(135deg, #0d47a1, #002171);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(21, 101, 192, 0.4);
}

/* ❌ Messages d'erreur */
.error {
  background: #e3f2fd;
  color: #0d47a1;
  border: 1px solid #1565c0;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 12px;
  font-size: 14px;
  animation: shake 0.3s ease-in-out;
}

/* 🎬 Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes shake {
  0% { transform: translateX(-5px); }
  25% { transform: translateX(5px); }
  50% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
  100% { transform: translateX(0); }
}

  </style>
</head>
<body>
  <div class="form-container">
    <h2>Évaluation Arrima — Québec</h2>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $err): ?>
        <div class="error"><?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST">
      <label>Nom complet :</label>
      <input type="text" name="nom" required>

      <label>Email :</label>
      <input type="email" name="email" required>

      <label>Âge :</label>
      <input type="number" name="age" min="18" max="100" required>

      <label>Niveau d'éducation :</label>
      <select name="education" required>
        <option value="">-- Choisir --</option>
        <option value="secondary">Secondaire</option>
        <option value="college">Collégial</option>
        <option value="bachelor">Licence</option>
        <option value="master">Master</option>
        <option value="phd">Doctorat</option>
      </select>

      <label>Années d'expérience :</label>
      <input type="number" name="experience" min="0" required>

      <label>Niveau de français (CLB) :</label>
      <input type="number" name="french" min="0" max="12" required>

      <label>Niveau d'anglais (CLB) :</label>
      <input type="number" name="english" min="0" max="12" required>

      <label>Situation familiale :</label>
      <select name="situation_familiale" required>
        <option value="">-- Choisir --</option>
        <option value="celibataire">Célibataire</option>
        <option value="marie">Marié(e)</option>
        <option value="divorce">Divorcé(e)</option>
      </select>

      <label>Nombre d'enfants :</label>
      <input type="number" name="enfants" min="0">

      <label>Offre d'emploi validée au Québec :</label>
      <select name="offre_emploi">
        <option value="non">Non</option>
        <option value="oui">Oui</option>
      </select>

      <label>Famille au Québec :</label>
      <select name="famille_canada">
        <option value="non">Non</option>
        <option value="oui">Oui</option>
      </select>

      <label>
        <input type="checkbox" name="consentement" required> J'accepte le traitement de mes données
      </label>

      <button type="submit" class="btn-submit">Évaluer</button>
    </form>
  </div>
</body>
</html>
