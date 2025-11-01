<?php
header('Content-Type: application/json; charset=utf-8');

// Configurazione database
$host = 'localhost';
$dbname = 'comune_segnalazioni';
$username = 'root';
$password = ''; // 👈 Cambia con la tua password se ce l’hai

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Errore connessione DB: ' . $e->getMessage()]));
}

// Recupera l'id dalla query string
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die(json_encode(['success' => false, 'message' => 'ID non valido']));
}

// Recupera i dati della segnalazione
$stmt = $pdo->prepare("
    SELECT * FROM segnalazioni WHERE id = :id
");
$stmt->execute([':id' => $id]);
$segnalazione = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$segnalazione) {
    die(json_encode(['success' => false, 'message' => 'Segnalazione non trovata']));
}

echo json_encode([
    'success' => true,
    'segnalazione' => $segnalazione
]);