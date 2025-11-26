<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit();
}

$id = intval($_GET['id']);

try {
    // Récupérer les détails du test
    $stmt = $pdo->prepare("SELECT * FROM langue_tests WHERE id = ?");
    $stmt->execute([$id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$test) {
        echo json_encode(['success' => false, 'message' => 'Test non trouvé']);
        exit();
    }
    
    // Fonctions de formatage
    function formatStatut($statut) {
        $statuts = [
            'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
            'confirme' => ['label' => 'Confirmé', 'class' => 'success', 'icon' => 'check-circle'],
            'annule' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'times-circle'],
            'termine' => ['label' => 'Terminé', 'class' => 'info', 'icon' => 'flag-checkered']
        ];
        return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
    }
    
    function formatTypeTest($type_test) {
        $types = [
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
            'ielts_academic' => 'IELTS Academic',
            'ielts_general' => 'IELTS General',
            'toefl_ibt' => 'TOEFL iBT',
            'toeic' => 'TOEIC',
            'cambridge_b2' => 'Cambridge B2',
            'cambridge_c1' => 'Cambridge C1',
            'celpip_general' => 'CELPIP General',
            'pte_academic' => 'PTE Academic'
        ];
        return $types[$type_test] ?? $type_test;
    }
    
    $statut = formatStatut($test['statut']);
    
    // Générer le HTML des détails
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-user me-2"></i>Informations du candidat
            </h6>
            <table class="table table-sm">
                <tr>
                    <td class="fw-bold" width="40%">Nom complet:</td>
                    <td>' . htmlspecialchars($test['prenom'] . ' ' . $test['nom']) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Email:</td>
                    <td>' . htmlspecialchars($test['email']) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Téléphone:</td>
                    <td>' . htmlspecialchars($test['telephone']) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Ville:</td>
                    <td>' . htmlspecialchars($test['ville']) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Pays:</td>
                    <td>' . htmlspecialchars($test['pays']) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-calendar me-2"></i>Détails du test
            </h6>
            <table class="table table-sm">
                <tr>
                    <td class="fw-bold" width="40%">Type de test:</td>
                    <td>' . formatTypeTest($test['type_test']) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Date:</td>
                    <td>' . date('d/m/Y', strtotime($test['date_rendezvous'])) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Heure:</td>
                    <td>' . date('H:i', strtotime($test['heure_rendezvous'])) . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Statut:</td>
                    <td>
                        <span class="badge bg-' . $statut['class'] . '">
                            <i class="fas fa-' . $statut['icon'] . ' me-1"></i>
                            ' . $statut['label'] . '
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Date création:</td>
                    <td>' . date('d/m/Y H:i', strtotime($test['date_creation'])) . '</td>
                </tr>
            </table>
        </div>
    </div>';
    
    // Section notes du candidat
    if (!empty($test['notes_candidat'])) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-2">
                    <i class="fas fa-sticky-note me-2"></i>Notes du candidat
                </h6>
                <div class="bg-light p-3 rounded">
                    ' . nl2br(htmlspecialchars($test['notes_candidat'])) . '
                </div>
            </div>
        </div>';
    }
    
    // Section notes administratives
    $html .= '
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-edit me-2"></i>Notes administratives
            </h6>
            <form id="notesForm" method="POST">
                <input type="hidden" name="action" value="ajouter_notes">
                <input type="hidden" name="id" value="' . $test['id'] . '">
                <div class="mb-3">
                    <textarea name="notes_admin" class="form-control" rows="4" placeholder="Ajoutez vos notes administratives ici...">' . htmlspecialchars($test['notes_admin'] ?? '') . '</textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="changerStatutModal(' . $test['id'] . ', \'confirme\')">
                            <i class="fas fa-check me-1"></i>Confirmer
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="changerStatutModal(' . $test['id'] . ', \'en_attente\')">
                            <i class="fas fa-clock me-1"></i>En attente
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="changerStatutModal(' . $test['id'] . ', \'annule\')">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-dark btn-sm" onclick="supprimerTestModal(' . $test['id'] . ')">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Enregistrer les notes
                    </button>
                </div>
            </form>
        </div>
    </div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
}
?>