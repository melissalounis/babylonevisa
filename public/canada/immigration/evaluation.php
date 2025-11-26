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
    $english = intval($_POST['english'] ?? 0);
    $french = intval($_POST['french'] ?? 0);
    $situation_familiale = $_POST['situation_familiale'] ?? '';
    $enfants = intval($_POST['enfants'] ?? 0);
    $province = $_POST['province'] ?? '';
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
        $score = calculateExpressEntryPoints($age, $education, $experience, $english, $french);
        $is_eligible = $score >= 67 ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO evaluations_immigration 
            (nom, email, age, education, experience, english_level, french_level, 
             situation_familiale, enfants, province, offre_emploi, famille_canada, 
             programme, score, eligible) 
            VALUES (:nom, :email, :age, :education, :experience, :english, :french,
             :situation_familiale, :enfants, :province, :offre_emploi, :famille_canada,
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
            ':province' => $province,
            ':offre_emploi' => $offre_emploi,
            ':famille_canada' => $famille_canada,
            ':programme' => 'express',
            ':score' => $score,
            ':eligible' => $is_eligible
        ]);

        $_SESSION['evaluation_score'] = $score;
        $_SESSION['is_eligible'] = $is_eligible;

        if ($is_eligible) {
            header("Location: documents.php");
        } else {
            header("Location: ineligible.php");
        }
        exit;
    }
}

// Fonction de calcul
function calculateExpressEntryPoints($age, $education, $experience, $english, $french) {
    $points = 0;

    if ($age >= 18 && $age <= 35) $points += 12;
    elseif ($age == 36) $points += 11;
    elseif ($age == 37) $points += 10;
    elseif ($age == 38) $points += 9;
    elseif ($age == 39) $points += 8;
    elseif ($age == 40) $points += 7;
    elseif ($age == 41) $points += 6;
    elseif ($age == 42) $points += 5;
    elseif ($age == 43) $points += 4;
    elseif ($age == 44) $points += 3;
    elseif ($age == 45) $points += 2;
    elseif ($age == 46) $points += 1;

    $educationPoints = [
        'secondary' => 15,
        'post-secondary' => 19,
        'bachelor' => 21,
        'master' => 23,
        'phd' => 25
    ];
    $points += $educationPoints[$education] ?? 0;

    if ($experience >= 6) $points += 15;
    elseif ($experience >= 4) $points += 13;
    elseif ($experience >= 2) $points += 11;
    elseif ($experience >= 1) $points += 9;

    if ($english >= 9) $points += 24;
    elseif ($english >= 8) $points += 22;
    elseif ($english >= 7) $points += 20;

    if ($french >= 9) $points += 24;
    elseif ($french >= 8) $points += 22;
    elseif ($french >= 7) $points += 20;

    return $points;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Évaluation — Entrée Express</title>
 <style>
    /* 🌍 Thème Canada */
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(135deg, #f5f5f5, #ffffff);
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
  border-top: 6px solid #d32f2f;
  position: relative;
  animation: fadeIn 0.8s ease-in-out;
}

/* 🍁 Logo style drapeau Canada */
.form-container::before {
  content: "🍁";
  font-size: 42px;
  position: absolute;
  top: -25px;
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  padding: 5px 15px;
  border-radius: 50%;
  border: 3px solid #d32f2f;
}

/* 📝 Titre */
h2 {
  text-align: center;
  color: #d32f2f;
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
  border-color: #d32f2f;
  box-shadow: 0 0 8px rgba(211, 47, 47, 0.3);
}

/* ✅ Checkbox */
input[type="checkbox"] {
  width: auto;
  margin-right: 8px;
  transform: scale(1.2);
}

/* 🔴 Bouton */
.btn-submit {
  background: linear-gradient(135deg, #d32f2f, #8e0000);
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
  background: linear-gradient(135deg, #b71c1c, #600000);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(211, 47, 47, 0.4);
}

/* ❌ Messages d'erreur */
.error {
  background: #ffebee;
  color: #b71c1c;
  border: 1px solid #d32f2f;
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
    <h2>Évaluation Entrée Express</h2>

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
        <option value="post-secondary">Post-secondaire</option>
        <option value="bachelor">Licence</option>
        <option value="master">Master</option>
        <option value="phd">Doctorat</option>
      </select>

      <label>Années d'expérience :</label>
      <input type="number" name="experience" min="0" required>

      <label>Niveau d'anglais (CLB) :</label>
      <input type="number" name="english" min="0" max="12" required>

      <label>Niveau de français (CLB) :</label>
      <input type="number" name="french" min="0" max="12" required>

      <label>Situation familiale :</label>
      <select name="situation_familiale" required>
        <option value="">-- Choisir --</option>
        <option value="celibataire">Célibataire</option>
        <option value="marie">Marié(e)</option>
        <option value="divorce">Divorcé(e)</option>
      </select>

      <label>Nombre d'enfants :</label>
      <input type="number" name="enfants" min="0">

      <label>Province souhaitée :</label>
      <input type="text" name="province">

      <label>Offre d'emploi valide :</label>
      <select name="offre_emploi">
        <option value="non">Non</option>
        <option value="oui">Oui</option>
      </select>

      <label>Famille au Canada :</label>
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
