<?php
$host = 'localhost';
$dbname = 'comune_segnalazioni';
$username = 'Paesio_user';
$password = 'Moruccetto0007'; // 👈 sostituisci con la tua

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    echo "✅ Connessione al database riuscita!";
} catch (PDOException $e) {
    echo "❌ Errore: " . $e->getMessage();
}