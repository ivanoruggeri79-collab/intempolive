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
$nome_completo = trim($_POST['nome_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validazione base
if (empty($nome_completo) || empty($email) || empty($password) || empty($confirm_password)) {
    die(json_encode(['success' => false, 'message' => 'Tutti i campi sono obbligatori']));
}

if ($password !== $confirm_password) {
    die(json_encode(['success' => false, 'message' => 'Le password non coincidono']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'Email non valida']));
}

// Hash della password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Controllo se email già esiste
$stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = :email");
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    die(json_encode(['success' => false, 'message' => 'Email già registrata']));
}

// Inserimento utente
$stmt = $pdo->prepare("
    INSERT INTO utenti (nome_completo, email, password_hash, attivo)
    VALUES (:nome_completo, :email, :password_hash, 0)
");

$stmt->execute([
    ':nome_completo' => $nome_completo,
    ':email' => $email,
    ':password_hash' => $password_hash
]);

$user_id = $pdo->lastInsertId();

// Invia email di conferma
$to = $email;
$subject = "✅ Conferma registrazione a Paesio";
$message = "
    Ciao {$nome_completo},

    Grazie per esserti registrato su Paesio!

    Per completare la registrazione, clicca sul link qui sotto:
    http://localhost/Paesio/verify.php?token={$user_id}

    Se non hai richiesto questa registrazione, ignora questa email.

    Saluti,
    Team Paesio
";
$headers = "From: no-reply@paesio.local\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Prova a inviare l'email
if (mail($to, $subject, $message, $headers)) {
    $response = [
        'success' => true,
        'message' => 'Registrazione avvenuta con successo! Controlla la tua email per attivare l\'account.',
        'user_id' => $user_id,
        'redirect' => 'login.html'
    ];
} else {
    $response = [
        'success' => true,
        'message' => 'Registrazione avvenuta con successo! (Email non inviata - controlla la configurazione SMTP)',
        'user_id' => $user_id,
        'redirect' => 'login.html'
    ];
}

echo json_encode($response);