<?php
header('Content-Type: text/html; charset=utf-8');

// Configurazione database
$host = 'localhost';
$dbname = 'comune_segnalazioni';
$username = 'root';
$password = ''; // 👈 Cambia con la tua password se ce l’hai

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Errore connessione DB: " . $e->getMessage());
}

// Recupera il token (user_id)
$token = intval($_GET['token'] ?? 0);

if ($token <= 0) {
    die("<h1>❌ Token non valido</h1><p>Il link di attivazione non è valido.</p>");
}

// Attiva l’utente (aggiungi un campo `attivo` se non c’è)
$stmt = $pdo->prepare("UPDATE utenti SET attivo = 1 WHERE id = :id");
$stmt->execute([':id' => $token]);

echo "<h1>✅ Account attivato con successo!</h1>";
echo "<p>Grazie per esserti registrato su Paesio. Ora puoi accedere con la tua email e password.</p>";
echo "<a href='login.html'>Accedi ora</a>";