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

// Recupera le segnalazioni approvate
$stmt = $pdo->prepare("
    SELECT s.*, u.nome_completo as nome_utente
    FROM segnalazioni s
    LEFT JOIN utenti u ON s.utente_id = u.id
    WHERE s.stato = 'approvata'
    ORDER BY s.data_approvazione DESC
");

$stmt->execute();
$segnalazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'segnalazioni' => $segnalazioni
]);