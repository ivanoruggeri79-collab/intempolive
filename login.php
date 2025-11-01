<?php
session_start();
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
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validazione
if (empty($email) || empty($password)) {
    die(json_encode(['success' => false, 'message' => 'Email e password obbligatorie']));
}

// Cerca utente
$stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die(json_encode(['success' => false, 'message' => 'Email o password errati']));
}

// Verifica password
if (!password_verify($password, $user['password_hash'])) {
    die(json_encode(['success' => false, 'message' => 'Email o password errati']));
}

// Avvia sessione
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = $user['id'];
$_SESSION['nome_completo'] = $user['nome_completo'];
$_SESSION['email'] = $user['email'];

// Aggiorna ultimo accesso
$stmt = $pdo->prepare("UPDATE utenti SET ultimo_accesso = NOW() WHERE id = :id");
$stmt->execute([':id' => $user['id']]);

// Risposta JSON
echo json_encode([
    'success' => true,
    'message' => 'Accesso effettuato con successo!',
    'redirect' => 'index.html' // Puoi cambiare in una pagina dashboard
]);