<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Accesso negato");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metodo non consentito");
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    die("Parametri non validi");
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=comune_segnalazioni;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $new_status = $action === 'approve' ? 'approvata' : 'rifiutata';
    $stmt = $pdo->prepare("UPDATE segnalazioni SET stato = :stato, data_approvazione = NOW() WHERE id = :id");
    $stmt->execute([
        ':stato' => $new_status,
        ':id' => $id
    ]);

    // Redirect alla dashboard con messaggio
    $message = $action === 'approve' ? 'Segnalazione approvata!' : 'Segnalazione rifiutata!';
    header("Location: index.php?msg=" . urlencode($message));
    exit;

} catch (PDOException $e) {
    die("Errore: " . $e->getMessage());
}
