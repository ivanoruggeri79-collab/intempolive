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

// Dati del form
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    die(json_encode(['success' => false, 'message' => 'ID non valido']));
}

// Incrementa i like
$stmt = $pdo->prepare("UPDATE segnalazioni SET likes = likes + 1 WHERE id = :id");
$stmt->execute([':id' => $id]);

// Recupera il nuovo numero di like
$stmt = $pdo->prepare("SELECT likes FROM segnalazioni WHERE id = :id");
$stmt->execute([':id' => $id]);
$likes = $stmt->fetch()['likes'];

echo json_encode([
    'success' => true,
    'likes' => $likes
]);